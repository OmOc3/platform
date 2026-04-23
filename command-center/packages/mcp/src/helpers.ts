import {
  calculateDriftDays,
  milestoneIsComplete,
  type Agent,
  type AgentLogEntry,
  type Milestone,
  type Subtask,
  type TaskStatus,
  type TrackerState,
} from '@command-center/tracker-core';

export interface TaskLookup {
  subtask: Subtask;
  milestone: Milestone;
  subtaskIndex: number;
  milestoneIndex: number;
}

export const formatDate = (value: string | null): string => value ?? '—';

export const randomId = (prefix: string): string =>
  `${prefix}_${Date.now()}_${Math.random().toString(36).slice(2, 8)}`;

export const cloneState = (state: TrackerState): TrackerState => structuredClone(state);

export const findMilestone = (state: TrackerState, milestoneId: string) => {
  const milestoneIndex = state.milestones.findIndex((milestone) => milestone.id === milestoneId);

  if (milestoneIndex === -1) {
    return null;
  }

  return {
    milestone: state.milestones[milestoneIndex],
    milestoneIndex,
  };
};

export const findTask = (state: TrackerState, taskId: string): TaskLookup | null => {
  for (const [milestoneIndex, milestone] of state.milestones.entries()) {
    const subtaskIndex = milestone.subtasks.findIndex((task) => task.id === taskId);

    if (subtaskIndex !== -1) {
      return {
        subtask: milestone.subtasks[subtaskIndex],
        milestone,
        subtaskIndex,
        milestoneIndex,
      };
    }
  }

  return null;
};

export const createTaskId = (milestone: Milestone): string =>
  `${milestone.id}_${String(milestone.subtasks.length + 1).padStart(3, '0')}`;

export const inferAgentType = (agentId: string): Agent['type'] => {
  if (agentId === 'operator') return 'human';
  if (agentId === 'orchestrator') return 'orchestrator';
  if (agentId.includes('agent') || agentId.includes('auditor') || agentId.includes('researcher')) {
    return 'sub-agent';
  }

  return 'external';
};

export const touchAgent = (
  state: TrackerState,
  agentId = 'orchestrator',
  overrides?: Partial<Agent>,
): void => {
  const now = new Date().toISOString();
  const existing = state.agents.find((agent) => agent.id === agentId);

  if (existing) {
    existing.last_action_at = now;
    existing.session_action_count += 1;
    existing.status = 'active';

    if (overrides) {
      Object.assign(existing, overrides);
    }

    return;
  }

  state.agents.push({
    id: agentId,
    name: overrides?.name ?? agentId,
    type: overrides?.type ?? inferAgentType(agentId),
    parent_id: overrides?.parent_id,
    color: overrides?.color ?? '#9B9BAA',
    status: 'active',
    permissions: overrides?.permissions ?? ['read'],
    last_action_at: now,
    session_action_count: 1,
  });
};

export const appendLog = (
  state: TrackerState,
  entry: Omit<AgentLogEntry, 'id' | 'timestamp'>,
): AgentLogEntry => {
  const logEntry: AgentLogEntry = {
    ...entry,
    id: randomId('log'),
    timestamp: new Date().toISOString(),
  };

  state.agent_log.push(logEntry);

  return logEntry;
};

export const ensureOperator = (actorRole?: string): void => {
  if (actorRole !== 'operator') {
    throw new Error('This action is restricted to the operator.');
  }
};

export const setTaskStatus = (task: Subtask, status: TaskStatus): void => {
  task.status = status;
  task.done = status === 'done';

  if (status !== 'done') {
    task.completed_at = null;
    task.completed_by = null;
  }
};

export const autoUnblockDependents = (
  state: TrackerState,
  completedTaskId: string,
  completedMilestoneId: string,
): string[] => {
  const changes: string[] = [];

  for (const milestone of state.milestones) {
    for (const task of milestone.subtasks) {
      if (!task.depends_on.includes(completedTaskId)) {
        continue;
      }

      const allDone = task.depends_on.every((dependencyId) => {
        const dependency = findTask(state, dependencyId);
        return dependency?.subtask.status === 'done';
      });

      if (allDone && task.status === 'blocked') {
        task.status = 'todo';
        task.blocked_by = null;
        task.blocked_reason = null;
        changes.push(`Unblocked task ${task.id} after dependencies completed.`);
      }
    }
  }

  const completedMilestone = state.milestones.find((milestone) => milestone.id === completedMilestoneId);

  if (completedMilestone && milestoneIsComplete(completedMilestone)) {
    for (const downstream of state.milestones) {
      if (!downstream.dependencies.includes(completedMilestoneId)) {
        continue;
      }

      const milestoneDepsDone = downstream.dependencies.every((dependencyId) => {
        const dependency = state.milestones.find((milestone) => milestone.id === dependencyId);
        return dependency ? milestoneIsComplete(dependency) : false;
      });

      if (!milestoneDepsDone) {
        continue;
      }

      for (const task of downstream.subtasks) {
        if (task.status === 'blocked') {
          task.status = 'todo';
          task.blocked_by = null;
          task.blocked_reason = null;
          changes.push(`Unblocked downstream task ${task.id} after milestone dependencies cleared.`);
        }
      }
    }
  }

  return changes;
};

export const syncMilestoneDates = (milestone: Milestone): void => {
  const done = milestone.subtasks.filter((task) => task.status === 'done');
  const inProgress = milestone.subtasks.some((task) =>
    ['in_progress', 'review', 'done', 'blocked'].includes(task.status),
  );

  if (inProgress && !milestone.actual_start) {
    milestone.actual_start = new Date().toISOString().slice(0, 10);
  }

  milestone.drift_days = calculateDriftDays(milestone.planned_start, milestone.actual_start);

  if (milestone.subtasks.length > 0 && done.length === milestone.subtasks.length) {
    milestone.actual_end = new Date().toISOString().slice(0, 10);
  } else if (milestone.actual_end) {
    milestone.actual_end = null;
  }
};

export const describeTask = (task: Subtask, milestone: Milestone): string =>
  `${task.label} (${task.id}) in ${milestone.title}`;
