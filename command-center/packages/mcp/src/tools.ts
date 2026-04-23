import {
  getMilestoneProgress,
  type Agent,
  type ExecutionMode,
  type Milestone,
  type TaskStatus,
  type TrackerState,
} from '@command-center/tracker-core';
import { ensureTrackerFile, readTracker, updateTracker } from '@command-center/tracker-core/node';
import { z, ZodError } from 'zod';

import { buildMilestoneOverview, buildProjectStatus, buildTaskContext, buildTaskSummary } from './context.js';
import {
  appendLog,
  autoUnblockDependents,
  cloneState,
  createTaskId,
  describeTask,
  ensureOperator,
  findMilestone,
  findTask,
  formatDate,
  setTaskStatus,
  syncMilestoneDates,
  touchAgent,
} from './helpers.js';

export interface ToolRuntimeContext {
  projectRoot: string;
}

interface ToolDefinition<TSchema extends z.ZodTypeAny> {
  name: string;
  description: string;
  inputSchema: Record<string, unknown>;
  schema: TSchema;
  handler: (args: z.infer<TSchema>, ctx: ToolRuntimeContext) => Promise<string>;
}

const actorRoleSchema = z.enum(['operator', 'agent']).optional();

const parseTags = (tags: string[] | undefined): string[] => {
  const values = tags ?? [];
  return Array.from(new Set([...values, 'mcp']));
};

const withState = async <T>(
  ctx: ToolRuntimeContext,
  updater: (state: TrackerState) => T | Promise<T>,
) => updater(await ensureTrackerFile(ctx.projectRoot));

const markdownError = (message: string) => ({
  content: [{ type: 'text' as const, text: message }],
  isError: true,
});

const textResult = (text: string) => ({
  content: [{ type: 'text' as const, text }],
});

const serializeTaskList = (tasks: Array<{ milestone: Milestone; task: Milestone['subtasks'][number] }>) =>
  tasks.map(({ milestone, task }) => `- [${task.status}] ${task.label} (${task.id}) · ${milestone.title} · ${task.priority}`);

const serializeAgent = (agent: Agent, weeklyActions: number) =>
  `- ${agent.name} (${agent.id}) · ${agent.type} · ${agent.status} · ${weeklyActions} actions this week`;

const readToolDefinitions: Array<ToolDefinition<any>> = [
  {
    name: 'get_task_context',
    description: 'Return full markdown context for a subtask.',
    inputSchema: {
      type: 'object',
      properties: { task_id: { type: 'string' } },
      required: ['task_id'],
    },
    schema: z.object({ task_id: z.string().min(1) }),
    handler: async ({ task_id }, ctx) =>
      withState(ctx, async (state) => {
        const lookup = findTask(state, task_id);
        if (!lookup) {
          throw new Error(`Task '${task_id}' not found in any milestone.`);
        }
        return buildTaskContext(state, lookup.subtask, lookup.milestone);
      }),
  },
  {
    name: 'get_task_summary',
    description: 'Return compact markdown summary for a subtask.',
    inputSchema: {
      type: 'object',
      properties: { task_id: { type: 'string' } },
      required: ['task_id'],
    },
    schema: z.object({ task_id: z.string().min(1) }),
    handler: async ({ task_id }, ctx) =>
      withState(ctx, async (state) => {
        const lookup = findTask(state, task_id);
        if (!lookup) {
          throw new Error(`Task '${task_id}' not found in any milestone.`);
        }
        return buildTaskSummary(state, lookup.subtask, lookup.milestone);
      }),
  },
  {
    name: 'get_project_status',
    description: 'Return project-wide tracker summary.',
    inputSchema: { type: 'object', properties: {} },
    schema: z.object({}).default({}),
    handler: async (_args, ctx) =>
      withState(ctx, async (state) => buildProjectStatus(state)),
  },
  {
    name: 'get_milestone_overview',
    description: 'Return milestone summary and task list.',
    inputSchema: {
      type: 'object',
      properties: { milestone_id: { type: 'string' } },
      required: ['milestone_id'],
    },
    schema: z.object({ milestone_id: z.string().min(1) }),
    handler: async ({ milestone_id }, ctx) =>
      withState(ctx, async (state) => {
        const lookup = findMilestone(state, milestone_id);
        if (!lookup) {
          throw new Error(`Milestone '${milestone_id}' not found.`);
        }
        return buildMilestoneOverview(lookup.milestone, state);
      }),
  },
  {
    name: 'list_tasks',
    description: 'List tasks across milestones with optional filters.',
    inputSchema: {
      type: 'object',
      properties: {
        milestone_id: { type: 'string' },
        status: { type: 'string' },
        domain: { type: 'string' },
      },
    },
    schema: z.object({
      milestone_id: z.string().min(1).optional(),
      status: z.enum(['todo', 'in_progress', 'review', 'done', 'blocked']).optional(),
      domain: z.string().min(1).optional(),
    }),
    handler: async ({ milestone_id, status, domain }, ctx) =>
      withState(ctx, async (state) => {
        const tasks = state.milestones
          .filter((milestone) => (!milestone_id || milestone.id === milestone_id) && (!domain || milestone.domain === domain))
          .flatMap((milestone) =>
            milestone.subtasks
              .filter((task) => !status || task.status === status)
              .map((task) => ({ milestone, task })),
          );

        return [
          '# Task List',
          '',
          ...(tasks.length > 0 ? serializeTaskList(tasks) : ['- No tasks matched the provided filters.']),
        ].join('\n');
      }),
  },
  {
    name: 'get_task_history',
    description: 'Return activity history for a subtask.',
    inputSchema: {
      type: 'object',
      properties: { task_id: { type: 'string' } },
      required: ['task_id'],
    },
    schema: z.object({ task_id: z.string().min(1) }),
    handler: async ({ task_id }, ctx) =>
      withState(ctx, async (state) => {
        const entries = state.agent_log
          .filter((entry) => entry.target_id === task_id)
          .sort((a, b) => a.timestamp.localeCompare(b.timestamp));

        return [
          `# History for ${task_id}`,
          '',
          ...(entries.length > 0
            ? entries.map((entry) => `- ${entry.timestamp} · ${entry.agent_id} · ${entry.action} · ${entry.description}`)
            : ['- No history found.']),
        ].join('\n');
      }),
  },
  {
    name: 'list_agents',
    description: 'List known agents with recent activity.',
    inputSchema: { type: 'object', properties: {} },
    schema: z.object({}).default({}),
    handler: async (_args, ctx) =>
      withState(ctx, async (state) => {
        const since = Date.now() - 7 * 24 * 60 * 60 * 1000;
        const lines = state.agents.map((agent) => {
          const weeklyActions = state.agent_log.filter(
            (entry) => entry.agent_id === agent.id && Date.parse(entry.timestamp) >= since,
          ).length;

          return serializeAgent(agent, weeklyActions);
        });

        return ['# Agents', '', ...(lines.length > 0 ? lines : ['- No agents registered.'])].join('\n');
      }),
  },
  {
    name: 'get_activity_feed',
    description: 'Return recent activity log entries.',
    inputSchema: {
      type: 'object',
      properties: {
        agent_id: { type: 'string' },
        limit: { type: 'number' },
      },
    },
    schema: z.object({
      agent_id: z.string().min(1).optional(),
      limit: z.number().int().positive().max(200).optional(),
    }),
    handler: async ({ agent_id, limit = 30 }, ctx) =>
      withState(ctx, async (state) => {
        const entries = state.agent_log
          .filter((entry) => !agent_id || entry.agent_id === agent_id)
          .sort((a, b) => b.timestamp.localeCompare(a.timestamp))
          .slice(0, limit);

        return [
          '# Activity Feed',
          '',
          ...(entries.length > 0
            ? entries.map((entry) => `- ${entry.timestamp} · ${entry.agent_id} · ${entry.action} · ${entry.description} [${entry.tags.join(', ')}]`)
            : ['- No activity found.']),
        ].join('\n');
      }),
  },
];

const writeToolDefinitions: Array<ToolDefinition<any>> = [
  {
    name: 'start_task',
    description: 'Move a task to in_progress and stamp a run ID.',
    inputSchema: {
      type: 'object',
      properties: {
        task_id: { type: 'string' },
        agent_id: { type: 'string' },
      },
      required: ['task_id'],
    },
    schema: z.object({
      task_id: z.string().min(1),
      agent_id: z.string().min(1).default('orchestrator'),
    }),
    handler: async ({ task_id, agent_id }, ctx) => {
      const next = await updateTracker(ctx.projectRoot, (current) => {
        const state = cloneState(current);
        const lookup = findTask(state, task_id);
        if (!lookup) {
          throw new Error(`Task '${task_id}' not found in any milestone.`);
        }

        lookup.subtask.status = 'in_progress';
        lookup.subtask.assignee = lookup.subtask.assignee ?? agent_id;
        lookup.subtask.last_run_id = `run_${Date.now()}`;
        syncMilestoneDates(lookup.milestone);
        appendLog(state, {
          agent_id,
          action: 'task_started',
          target_type: 'subtask',
          target_id: task_id,
          description: `Started ${describeTask(lookup.subtask, lookup.milestone)}.`,
          tags: ['start', 'mcp'],
        });
        touchAgent(state, agent_id);
        return state;
      });

      return `Task ${task_id} is now in progress. Tracker version: ${next.meta.version}.`;
    },
  },
  {
    name: 'complete_task',
    description: 'Submit a task for operator review.',
    inputSchema: {
      type: 'object',
      properties: {
        task_id: { type: 'string' },
        summary: { type: 'string' },
        agent_id: { type: 'string' },
      },
      required: ['task_id', 'summary'],
    },
    schema: z.object({
      task_id: z.string().min(1),
      summary: z.string().min(1),
      agent_id: z.string().min(1).default('orchestrator'),
    }),
    handler: async ({ task_id, summary, agent_id }, ctx) => {
      const next = await updateTracker(ctx.projectRoot, (current) => {
        const state = cloneState(current);
        const lookup = findTask(state, task_id);
        if (!lookup) {
          throw new Error(`Task '${task_id}' not found in any milestone.`);
        }

        lookup.subtask.status = 'review';
        lookup.subtask.blocked_by = null;
        lookup.subtask.blocked_reason = null;
        appendLog(state, {
          agent_id,
          action: 'task_submitted_for_review',
          target_type: 'subtask',
          target_id: task_id,
          description: summary,
          tags: ['review', 'mcp'],
        });
        touchAgent(state, agent_id);
        return state;
      });

      return `Task ${task_id} submitted for review. Tracker version: ${next.meta.version}.`;
    },
  },
  {
    name: 'approve_task',
    description: 'Operator-only approval that moves a reviewed task to done.',
    inputSchema: {
      type: 'object',
      properties: {
        task_id: { type: 'string' },
        feedback: { type: 'string' },
        agent_id: { type: 'string' },
        actor_role: { type: 'string' },
      },
      required: ['task_id'],
    },
    schema: z.object({
      task_id: z.string().min(1),
      feedback: z.string().optional(),
      agent_id: z.string().min(1).default('operator'),
      actor_role: actorRoleSchema,
    }),
    handler: async ({ task_id, feedback, agent_id, actor_role }, ctx) => {
      ensureOperator(actor_role);

      const next = await updateTracker(ctx.projectRoot, (current) => {
        const state = cloneState(current);
        const lookup = findTask(state, task_id);
        if (!lookup) {
          throw new Error(`Task '${task_id}' not found in any milestone.`);
        }
        if (lookup.subtask.status !== 'review') {
          throw new Error(`Task '${task_id}' is in status '${lookup.subtask.status}', expected 'review'.`);
        }

        setTaskStatus(lookup.subtask, 'done');
        lookup.subtask.completed_at = new Date().toISOString();
        lookup.subtask.completed_by = agent_id;
        lookup.subtask.blocked_by = null;
        lookup.subtask.blocked_reason = null;
        syncMilestoneDates(lookup.milestone);
        const changes = autoUnblockDependents(state, task_id, lookup.milestone.id);
        appendLog(state, {
          agent_id,
          action: 'task_approved',
          target_type: 'subtask',
          target_id: task_id,
          description: feedback ? `Approved with feedback: ${feedback}` : `Approved ${lookup.subtask.label}.`,
          tags: parseTags(changes.length > 0 ? ['approve', 'unblock'] : ['approve']),
        });
        touchAgent(state, agent_id, {
          type: 'human',
          permissions: ['approve', 'write', 'read'],
        });
        return state;
      });

      return `Task ${task_id} approved. Tracker version: ${next.meta.version}.`;
    },
  },
  {
    name: 'reject_task',
    description: 'Operator-only rejection that moves a reviewed task back to in_progress.',
    inputSchema: {
      type: 'object',
      properties: {
        task_id: { type: 'string' },
        feedback: { type: 'string' },
        agent_id: { type: 'string' },
        actor_role: { type: 'string' },
      },
      required: ['task_id', 'feedback'],
    },
    schema: z.object({
      task_id: z.string().min(1),
      feedback: z.string().min(1),
      agent_id: z.string().min(1).default('operator'),
      actor_role: actorRoleSchema,
    }),
    handler: async ({ task_id, feedback, agent_id, actor_role }, ctx) => {
      ensureOperator(actor_role);

      const next = await updateTracker(ctx.projectRoot, (current) => {
        const state = cloneState(current);
        const lookup = findTask(state, task_id);
        if (!lookup) {
          throw new Error(`Task '${task_id}' not found in any milestone.`);
        }
        if (lookup.subtask.status !== 'review') {
          throw new Error(`Task '${task_id}' is in status '${lookup.subtask.status}', expected 'review'.`);
        }

        lookup.subtask.status = 'in_progress';
        const revisionNumber = state.agent_log.filter(
          (entry) => entry.target_id === task_id && entry.action === 'revision_requested',
        ).length + 1;
        appendLog(state, {
          agent_id,
          action: 'revision_requested',
          target_type: 'subtask',
          target_id: task_id,
          description: `Revision ${revisionNumber}: ${feedback}`,
          tags: ['reject', 'mcp'],
        });
        touchAgent(state, agent_id, {
          type: 'human',
          permissions: ['approve', 'write', 'read'],
        });
        return state;
      });

      return `Task ${task_id} returned for revision. Tracker version: ${next.meta.version}.`;
    },
  },
  {
    name: 'reset_task',
    description: 'Operator-only hard reset of task execution fields.',
    inputSchema: {
      type: 'object',
      properties: {
        task_id: { type: 'string' },
        agent_id: { type: 'string' },
        actor_role: { type: 'string' },
      },
      required: ['task_id'],
    },
    schema: z.object({
      task_id: z.string().min(1),
      agent_id: z.string().min(1).default('operator'),
      actor_role: actorRoleSchema,
    }),
    handler: async ({ task_id, agent_id, actor_role }, ctx) => {
      ensureOperator(actor_role);

      const next = await updateTracker(ctx.projectRoot, (current) => {
        const state = cloneState(current);
        const lookup = findTask(state, task_id);
        if (!lookup) {
          throw new Error(`Task '${task_id}' not found in any milestone.`);
        }

        setTaskStatus(lookup.subtask, 'todo');
        lookup.subtask.assignee = null;
        lookup.subtask.blocked_by = null;
        lookup.subtask.blocked_reason = null;
        lookup.subtask.last_run_id = null;
        appendLog(state, {
          agent_id,
          action: 'task_reset',
          target_type: 'subtask',
          target_id: task_id,
          description: `Reset ${lookup.subtask.label} to todo.`,
          tags: ['reset', 'mcp'],
        });
        touchAgent(state, agent_id, {
          type: 'human',
          permissions: ['approve', 'write', 'read'],
        });
        return state;
      });

      return `Task ${task_id} reset to todo. Tracker version: ${next.meta.version}.`;
    },
  },
  {
    name: 'block_task',
    description: 'Mark a task as blocked with a reason.',
    inputSchema: {
      type: 'object',
      properties: {
        task_id: { type: 'string' },
        reason: { type: 'string' },
        agent_id: { type: 'string' },
      },
      required: ['task_id', 'reason'],
    },
    schema: z.object({
      task_id: z.string().min(1),
      reason: z.string().min(1),
      agent_id: z.string().min(1).default('orchestrator'),
    }),
    handler: async ({ task_id, reason, agent_id }, ctx) => {
      const next = await updateTracker(ctx.projectRoot, (current) => {
        const state = cloneState(current);
        const lookup = findTask(state, task_id);
        if (!lookup) {
          throw new Error(`Task '${task_id}' not found in any milestone.`);
        }

        lookup.subtask.status = 'blocked';
        lookup.subtask.blocked_by = agent_id;
        lookup.subtask.blocked_reason = reason;
        appendLog(state, {
          agent_id,
          action: 'task_blocked',
          target_type: 'subtask',
          target_id: task_id,
          description: reason,
          tags: ['blocked', 'mcp'],
        });
        touchAgent(state, agent_id);
        return state;
      });

      return `Task ${task_id} blocked. Tracker version: ${next.meta.version}.`;
    },
  },
  {
    name: 'unblock_task',
    description: 'Remove a task block and restore todo or in_progress.',
    inputSchema: {
      type: 'object',
      properties: {
        task_id: { type: 'string' },
        resolution: { type: 'string' },
        agent_id: { type: 'string' },
      },
      required: ['task_id'],
    },
    schema: z.object({
      task_id: z.string().min(1),
      resolution: z.string().optional(),
      agent_id: z.string().min(1).default('orchestrator'),
    }),
    handler: async ({ task_id, resolution, agent_id }, ctx) => {
      const next = await updateTracker(ctx.projectRoot, (current) => {
        const state = cloneState(current);
        const lookup = findTask(state, task_id);
        if (!lookup) {
          throw new Error(`Task '${task_id}' not found in any milestone.`);
        }
        if (lookup.subtask.status !== 'blocked') {
          throw new Error(`Task '${task_id}' is in status '${lookup.subtask.status}', expected 'blocked'.`);
        }

        lookup.subtask.status = lookup.subtask.last_run_id ? 'in_progress' : 'todo';
        lookup.subtask.blocked_by = null;
        lookup.subtask.blocked_reason = null;
        appendLog(state, {
          agent_id,
          action: 'task_unblocked',
          target_type: 'subtask',
          target_id: task_id,
          description: resolution ?? 'Block cleared.',
          tags: ['unblocked', 'mcp'],
        });
        touchAgent(state, agent_id);
        return state;
      });

      return `Task ${task_id} unblocked. Tracker version: ${next.meta.version}.`;
    },
  },
  {
    name: 'update_task',
    description: 'Update task metadata such as priority, assignee, notes, or execution mode.',
    inputSchema: {
      type: 'object',
      properties: {
        task_id: { type: 'string' },
        priority: { type: 'string' },
        assignee: { type: 'string' },
        execution_mode: { type: 'string' },
        notes: { type: 'string' },
        agent_id: { type: 'string' },
      },
      required: ['task_id'],
    },
    schema: z.object({
      task_id: z.string().min(1),
      priority: z.enum(['P1', 'P2', 'P3', 'P4']).optional(),
      assignee: z.string().optional(),
      execution_mode: z.enum(['human', 'agent', 'pair']).optional(),
      notes: z.string().optional(),
      agent_id: z.string().min(1).default('orchestrator'),
    }),
    handler: async ({ task_id, priority, assignee, execution_mode, notes, agent_id }, ctx) => {
      const next = await updateTracker(ctx.projectRoot, (current) => {
        const state = cloneState(current);
        const lookup = findTask(state, task_id);
        if (!lookup) {
          throw new Error(`Task '${task_id}' not found in any milestone.`);
        }

        const changes: string[] = [];

        if (priority) {
          lookup.subtask.priority = priority;
          changes.push(`priority=${priority}`);
        }
        if (typeof assignee !== 'undefined') {
          lookup.subtask.assignee = assignee === '' ? null : assignee;
          changes.push(`assignee=${lookup.subtask.assignee ?? 'none'}`);
        }
        if (execution_mode) {
          lookup.subtask.execution_mode = execution_mode;
          changes.push(`execution_mode=${execution_mode}`);
        }
        if (typeof notes !== 'undefined') {
          lookup.subtask.notes = notes === '' ? null : notes;
          changes.push('notes updated');
        }

        appendLog(state, {
          agent_id,
          action: 'task_updated',
          target_type: 'subtask',
          target_id: task_id,
          description: changes.length > 0 ? changes.join(', ') : 'No effective changes.',
          tags: ['update', 'mcp'],
        });
        touchAgent(state, agent_id);
        return state;
      });

      return `Task ${task_id} updated. Tracker version: ${next.meta.version}.`;
    },
  },
  {
    name: 'log_action',
    description: 'Append an explicit activity entry to the agent log.',
    inputSchema: {
      type: 'object',
      properties: {
        task_id: { type: 'string' },
        action: { type: 'string' },
        description: { type: 'string' },
        tags: { type: 'array', items: { type: 'string' } },
        agent_id: { type: 'string' },
      },
      required: ['task_id', 'action', 'description'],
    },
    schema: z.object({
      task_id: z.string().min(1),
      action: z.string().min(1),
      description: z.string().min(1),
      tags: z.array(z.string()).optional(),
      agent_id: z.string().min(1).default('orchestrator'),
    }),
    handler: async ({ task_id, action, description, tags, agent_id }, ctx) => {
      const next = await updateTracker(ctx.projectRoot, (current) => {
        const state = cloneState(current);
        appendLog(state, {
          agent_id,
          action,
          target_type: 'subtask',
          target_id: task_id,
          description,
          tags: parseTags(tags),
        });
        touchAgent(state, agent_id);
        return state;
      });

      return `Logged action '${action}' for ${task_id}. Tracker version: ${next.meta.version}.`;
    },
  },
  {
    name: 'enrich_task',
    description: 'Update prompt, references, acceptance criteria, and related task metadata.',
    inputSchema: {
      type: 'object',
      properties: {
        task_id: { type: 'string' },
        prompt: { type: 'string' },
        builder_prompt: { type: 'string' },
        acceptance_criteria: { type: 'array', items: { type: 'string' } },
        constraints: { type: 'array', items: { type: 'string' } },
        context_files: { type: 'array', items: { type: 'string' } },
        reference_docs: { type: 'array', items: { type: 'string' } },
        agent_id: { type: 'string' },
      },
      required: ['task_id'],
    },
    schema: z.object({
      task_id: z.string().min(1),
      prompt: z.string().optional(),
      builder_prompt: z.string().optional(),
      acceptance_criteria: z.array(z.string()).optional(),
      constraints: z.array(z.string()).optional(),
      context_files: z.array(z.string()).optional(),
      reference_docs: z.array(z.string()).optional(),
      agent_id: z.string().min(1).default('orchestrator'),
    }),
    handler: async ({ task_id, agent_id, ...changes }, ctx) => {
      const next = await updateTracker(ctx.projectRoot, (current) => {
        const state = cloneState(current);
        const lookup = findTask(state, task_id);
        if (!lookup) {
          throw new Error(`Task '${task_id}' not found in any milestone.`);
        }

        const changedFields: string[] = [];

        for (const [key, value] of Object.entries(changes)) {
          if (typeof value === 'undefined') continue;
          (lookup.subtask as Record<string, unknown>)[key] = value;
          changedFields.push(key);
        }

        appendLog(state, {
          agent_id,
          action: 'task_enriched',
          target_type: 'subtask',
          target_id: task_id,
          description: changedFields.length > 0 ? `Updated: ${changedFields.join(', ')}` : 'No effective changes.',
          tags: ['enrich', 'mcp'],
        });
        touchAgent(state, agent_id);
        return state;
      });

      return `Task ${task_id} enriched. Tracker version: ${next.meta.version}.`;
    },
  },
  {
    name: 'add_milestone_note',
    description: 'Append a note or exit criterion to a milestone.',
    inputSchema: {
      type: 'object',
      properties: {
        milestone_id: { type: 'string' },
        note: { type: 'string' },
        agent_id: { type: 'string' },
      },
      required: ['milestone_id', 'note'],
    },
    schema: z.object({
      milestone_id: z.string().min(1),
      note: z.string().min(1),
      agent_id: z.string().min(1).default('orchestrator'),
    }),
    handler: async ({ milestone_id, note, agent_id }, ctx) => {
      const next = await updateTracker(ctx.projectRoot, (current) => {
        const state = cloneState(current);
        const lookup = findMilestone(state, milestone_id);
        if (!lookup) {
          throw new Error(`Milestone '${milestone_id}' not found.`);
        }

        lookup.milestone.notes.push(note);
        appendLog(state, {
          agent_id,
          action: 'milestone_note_added',
          target_type: 'milestone',
          target_id: milestone_id,
          description: note,
          tags: ['note', 'mcp'],
        });
        touchAgent(state, agent_id);
        return state;
      });

      return `Added note to ${milestone_id}. Tracker version: ${next.meta.version}.`;
    },
  },
  {
    name: 'set_milestone_dates',
    description: 'Update milestone actual start/end dates and recalculate drift.',
    inputSchema: {
      type: 'object',
      properties: {
        milestone_id: { type: 'string' },
        actual_start: { type: 'string' },
        actual_end: { type: 'string' },
        agent_id: { type: 'string' },
      },
      required: ['milestone_id'],
    },
    schema: z.object({
      milestone_id: z.string().min(1),
      actual_start: z.string().regex(/^\d{4}-\d{2}-\d{2}$/).optional(),
      actual_end: z.string().regex(/^\d{4}-\d{2}-\d{2}$/).optional(),
      agent_id: z.string().min(1).default('orchestrator'),
    }),
    handler: async ({ milestone_id, actual_start, actual_end, agent_id }, ctx) => {
      const next = await updateTracker(ctx.projectRoot, (current) => {
        const state = cloneState(current);
        const lookup = findMilestone(state, milestone_id);
        if (!lookup) {
          throw new Error(`Milestone '${milestone_id}' not found.`);
        }

        if (typeof actual_start !== 'undefined') {
          lookup.milestone.actual_start = actual_start;
        }
        if (typeof actual_end !== 'undefined') {
          lookup.milestone.actual_end = actual_end;
        }
        syncMilestoneDates(lookup.milestone);
        appendLog(state, {
          agent_id,
          action: 'milestone_dates_set',
          target_type: 'milestone',
          target_id: milestone_id,
          description: `Actual dates updated for ${lookup.milestone.title}.`,
          tags: ['schedule', 'mcp'],
        });
        touchAgent(state, agent_id);
        return state;
      });

      return `Updated milestone dates for ${milestone_id}. Tracker version: ${next.meta.version}.`;
    },
  },
  {
    name: 'update_drift',
    description: 'Set an explicit drift value for a milestone.',
    inputSchema: {
      type: 'object',
      properties: {
        milestone_id: { type: 'string' },
        drift_days: { type: 'number' },
        agent_id: { type: 'string' },
      },
      required: ['milestone_id', 'drift_days'],
    },
    schema: z.object({
      milestone_id: z.string().min(1),
      drift_days: z.number().int(),
      agent_id: z.string().min(1).default('orchestrator'),
    }),
    handler: async ({ milestone_id, drift_days, agent_id }, ctx) => {
      const next = await updateTracker(ctx.projectRoot, (current) => {
        const state = cloneState(current);
        const lookup = findMilestone(state, milestone_id);
        if (!lookup) {
          throw new Error(`Milestone '${milestone_id}' not found.`);
        }

        const previous = lookup.milestone.drift_days;
        lookup.milestone.drift_days = drift_days;
        appendLog(state, {
          agent_id,
          action: 'drift_updated',
          target_type: 'milestone',
          target_id: milestone_id,
          description: `Drift updated ${previous} → ${drift_days}.`,
          tags: ['schedule', 'mcp'],
        });
        touchAgent(state, agent_id);
        return state;
      });

      return `Updated drift for ${milestone_id}. Tracker version: ${next.meta.version}.`;
    },
  },
  {
    name: 'create_milestone',
    description: 'Create a new milestone in the tracker.',
    inputSchema: {
      type: 'object',
      properties: {
        id: { type: 'string' },
        title: { type: 'string' },
        domain: { type: 'string' },
        week: { type: 'number' },
        phase: { type: 'string' },
        planned_start: { type: 'string' },
        planned_end: { type: 'string' },
        agent_id: { type: 'string' },
      },
      required: ['id', 'title'],
    },
    schema: z.object({
      id: z.string().min(1),
      title: z.string().min(1),
      domain: z.string().min(1).default('general'),
      week: z.number().int().positive().default(1),
      phase: z.string().min(1).optional(),
      planned_start: z.string().regex(/^\d{4}-\d{2}-\d{2}$/).optional(),
      planned_end: z.string().regex(/^\d{4}-\d{2}-\d{2}$/).optional(),
      agent_id: z.string().min(1).default('orchestrator'),
    }),
    handler: async ({ id, title, domain, week, phase, planned_start, planned_end, agent_id }, ctx) => {
      const next = await updateTracker(ctx.projectRoot, (current) => {
        const state = cloneState(current);

        if (state.milestones.some((milestone) => milestone.id === id)) {
          throw new Error(`Milestone '${id}' already exists.`);
        }

        state.milestones.push({
          id,
          title,
          domain,
          week,
          phase: phase ?? id,
          planned_start: planned_start ?? null,
          planned_end: planned_end ?? null,
          actual_start: null,
          actual_end: null,
          drift_days: 0,
          is_key_milestone: false,
          key_milestone_label: null,
          subtasks: [],
          dependencies: [],
          notes: [],
        });
        appendLog(state, {
          agent_id,
          action: 'milestone_created',
          target_type: 'milestone',
          target_id: id,
          description: `Created milestone ${title}.`,
          tags: ['milestone', 'mcp'],
        });
        touchAgent(state, agent_id);
        return state;
      });

      return `Created milestone ${id}. Tracker version: ${next.meta.version}.`;
    },
  },
  {
    name: 'add_milestone_task',
    description: 'Create a new subtask inside a milestone.',
    inputSchema: {
      type: 'object',
      properties: {
        milestone_id: { type: 'string' },
        label: { type: 'string' },
        priority: { type: 'string' },
        acceptance_criteria: { type: 'array', items: { type: 'string' } },
        constraints: { type: 'array', items: { type: 'string' } },
        depends_on: { type: 'array', items: { type: 'string' } },
        execution_mode: { type: 'string' },
        agent_id: { type: 'string' },
      },
      required: ['milestone_id', 'label'],
    },
    schema: z.object({
      milestone_id: z.string().min(1),
      label: z.string().min(1),
      priority: z.enum(['P1', 'P2', 'P3', 'P4']).default('P2'),
      acceptance_criteria: z.array(z.string()).optional(),
      constraints: z.array(z.string()).optional(),
      depends_on: z.array(z.string()).optional(),
      execution_mode: z.enum(['human', 'agent', 'pair']).default('agent'),
      agent_id: z.string().min(1).default('orchestrator'),
    }),
    handler: async (
      { milestone_id, label, priority, acceptance_criteria, constraints, depends_on, execution_mode, agent_id },
      ctx,
    ) => {
      const next = await updateTracker(ctx.projectRoot, (current) => {
        const state = cloneState(current);
        const lookup = findMilestone(state, milestone_id);
        if (!lookup) {
          throw new Error(`Milestone '${milestone_id}' not found.`);
        }

        const taskId = createTaskId(lookup.milestone);
        lookup.milestone.subtasks.push({
          id: taskId,
          label,
          status: 'todo',
          done: false,
          assignee: null,
          blocked_by: null,
          blocked_reason: null,
          completed_at: null,
          completed_by: null,
          priority,
          notes: null,
          prompt: null,
          context_files: [],
          reference_docs: [],
          acceptance_criteria: acceptance_criteria ?? [],
          constraints: constraints ?? [],
          agent_target: null,
          execution_mode,
          depends_on: depends_on ?? [],
          last_run_id: null,
          builder_prompt: null,
        });
        appendLog(state, {
          agent_id,
          action: 'task_created',
          target_type: 'subtask',
          target_id: taskId,
          description: `Created task '${label}' in milestone ${lookup.milestone.title}.`,
          tags: ['task', 'mcp'],
        });
        touchAgent(state, agent_id);
        return state;
      });

      const latestState = await readTracker(ctx.projectRoot);
      const milestone = latestState.milestones.find((item) => item.id === milestone_id)!;
      const taskId = milestone.subtasks[milestone.subtasks.length - 1]?.id ?? 'unknown';

      return `Created task ${taskId}. Tracker version: ${next.meta.version}.`;
    },
  },
  {
    name: 'register_agent',
    description: 'Create or update an agent entry in the tracker.',
    inputSchema: {
      type: 'object',
      properties: {
        agent_id: { type: 'string' },
        name: { type: 'string' },
        type: { type: 'string' },
        permissions: { type: 'array', items: { type: 'string' } },
        color: { type: 'string' },
        parent_id: { type: 'string' },
      },
      required: ['agent_id', 'name', 'type', 'permissions'],
    },
    schema: z.object({
      agent_id: z.string().min(1),
      name: z.string().min(1),
      type: z.enum(['orchestrator', 'sub-agent', 'human', 'external']),
      permissions: z.array(z.string()).min(1),
      color: z.string().regex(/^#([0-9a-fA-F]{6})$/).default('#9B9BAA'),
      parent_id: z.string().min(1).optional(),
    }),
    handler: async ({ agent_id, name, type, permissions, color, parent_id }, ctx) => {
      const next = await updateTracker(ctx.projectRoot, (current) => {
        const state = cloneState(current);
        const existing = state.agents.find((agent) => agent.id === agent_id);

        if (existing) {
          existing.name = name;
          existing.type = type;
          existing.permissions = permissions;
          existing.color = color;
          existing.parent_id = parent_id;
          existing.status = 'active';
          existing.last_action_at = new Date().toISOString();
          appendLog(state, {
            agent_id,
            action: 'agent_updated',
            target_type: 'agent',
            target_id: agent_id,
            description: `Updated agent ${name}.`,
            tags: ['agent', 'mcp'],
          });
        } else {
          state.agents.push({
            id: agent_id,
            name,
            type,
            permissions,
            color,
            parent_id,
            status: 'active',
            last_action_at: new Date().toISOString(),
            session_action_count: 0,
          });
          appendLog(state, {
            agent_id,
            action: 'agent_registered',
            target_type: 'agent',
            target_id: agent_id,
            description: `Registered agent ${name}.`,
            tags: ['agent', 'mcp'],
          });
        }

        touchAgent(state, agent_id, { name, type, permissions, color, parent_id });
        return state;
      });

      return `Registered agent ${agent_id}. Tracker version: ${next.meta.version}.`;
    },
  },
];

export const toolDefinitions = [...readToolDefinitions, ...writeToolDefinitions];

const toolMap = new Map(toolDefinitions.map((tool) => [tool.name, tool]));

export const listTools = () =>
  toolDefinitions.map((tool) => ({
    name: tool.name,
    description: tool.description,
    inputSchema: tool.inputSchema,
  }));

export const handleTool = async (
  name: string,
  args: unknown,
  ctx: ToolRuntimeContext,
): Promise<{ content: Array<{ type: 'text'; text: string }>; isError?: boolean }> => {
  const definition = toolMap.get(name);

  if (!definition) {
    return markdownError(`Unknown tool '${name}'.`);
  }

  try {
    const parsed = definition.schema.parse(args ?? {});
    const text = await definition.handler(parsed, ctx);
    return textResult(text);
  } catch (error) {
    if (error instanceof ZodError) {
      return markdownError(`Invalid arguments for '${name}': ${error.issues.map((issue) => issue.message).join('; ')}`);
    }

    const message = error instanceof Error ? error.message : 'Unknown tool error';
    return markdownError(message);
  }
};
