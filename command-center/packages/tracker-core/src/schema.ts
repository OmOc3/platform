import { z } from 'zod';

const datePattern = /^\d{4}-\d{2}-\d{2}$/;

const dateSchema = z.string().regex(datePattern, 'Expected YYYY-MM-DD');
const nullableDateSchema = dateSchema.nullable();
const nullableStringSchema = z.string().min(1).nullable();
const isoDateTimeSchema = z.string().datetime({ offset: true });
const nullableIsoDateTimeSchema = isoDateTimeSchema.nullable();

export const taskStatusSchema = z.enum(['todo', 'in_progress', 'review', 'done', 'blocked']);
export const executionModeSchema = z.enum(['human', 'agent', 'pair']);
export const scheduleStatusSchema = z.enum(['on_track', 'behind', 'ahead']);
export const agentTypeSchema = z.enum(['orchestrator', 'sub-agent', 'human', 'external']);

export const trackerMetaSchema = z.object({
  schema_version: z.literal(1),
  version: z.number().int().nonnegative(),
  last_updated_at: isoDateTimeSchema,
});

export const domainSchema = z.object({
  id: z.string().min(1),
  label: z.string().min(1),
  color: z.string().regex(/^#([0-9a-fA-F]{6})$/, 'Expected 6-digit hex color'),
});

export const projectMetaSchema = z.object({
  name: z.string().min(1),
  start_date: dateSchema,
  target_date: dateSchema,
  current_week: z.number().int().positive(),
  schedule_status: scheduleStatusSchema,
  overall_progress: z.number().min(0).max(1),
});

export const subtaskSchema = z.object({
  id: z.string().min(1),
  label: z.string().min(1),
  status: taskStatusSchema,
  done: z.boolean(),
  assignee: nullableStringSchema,
  blocked_by: nullableStringSchema,
  blocked_reason: nullableStringSchema,
  completed_at: nullableIsoDateTimeSchema,
  completed_by: nullableStringSchema,
  priority: z.enum(['P1', 'P2', 'P3', 'P4']),
  notes: nullableStringSchema,
  prompt: nullableStringSchema,
  context_files: z.array(z.string()),
  reference_docs: z.array(z.string()),
  acceptance_criteria: z.array(z.string()),
  constraints: z.array(z.string()),
  agent_target: nullableStringSchema,
  execution_mode: executionModeSchema,
  depends_on: z.array(z.string()),
  last_run_id: nullableStringSchema,
  builder_prompt: nullableStringSchema,
});

export const milestoneSchema = z.object({
  id: z.string().min(1),
  title: z.string().min(1),
  domain: z.string().min(1),
  week: z.number().int().positive(),
  phase: z.string().min(1),
  planned_start: nullableDateSchema,
  planned_end: nullableDateSchema,
  actual_start: nullableDateSchema,
  actual_end: nullableDateSchema,
  drift_days: z.number().int(),
  is_key_milestone: z.boolean(),
  key_milestone_label: nullableStringSchema,
  subtasks: z.array(subtaskSchema),
  dependencies: z.array(z.string()),
  notes: z.array(z.string()),
});

export const agentSchema = z.object({
  id: z.string().min(1),
  name: z.string().min(1),
  type: agentTypeSchema,
  parent_id: z.string().min(1).optional(),
  color: z.string().regex(/^#([0-9a-fA-F]{6})$/, 'Expected 6-digit hex color'),
  status: z.string().min(1),
  permissions: z.array(z.string()),
  last_action_at: nullableIsoDateTimeSchema,
  session_action_count: z.number().int().nonnegative(),
});

export const agentLogEntrySchema = z.object({
  id: z.string().min(1),
  agent_id: z.string().min(1),
  action: z.string().min(1),
  target_type: z.string().min(1),
  target_id: z.string().min(1),
  description: z.string().min(1),
  timestamp: isoDateTimeSchema,
  tags: z.array(z.string()),
});

export const phaseSchema = z.object({
  id: z.string().min(1),
  title: z.string().min(1),
  start_week: z.number().int().positive(),
  end_week: z.number().int().positive(),
});

export const trackerStateSchema = z.object({
  meta: trackerMetaSchema,
  project: projectMetaSchema,
  milestones: z.array(milestoneSchema),
  agents: z.array(agentSchema),
  agent_log: z.array(agentLogEntrySchema),
  domains: z.array(domainSchema),
  schedule: z.object({
    phases: z.array(phaseSchema),
  }),
});

export type TaskStatus = z.infer<typeof taskStatusSchema>;
export type ExecutionMode = z.infer<typeof executionModeSchema>;
export type ScheduleStatus = z.infer<typeof scheduleStatusSchema>;
export type AgentType = z.infer<typeof agentTypeSchema>;
export type TrackerMeta = z.infer<typeof trackerMetaSchema>;
export type Domain = z.infer<typeof domainSchema>;
export type ProjectMeta = z.infer<typeof projectMetaSchema>;
export type Subtask = z.infer<typeof subtaskSchema>;
export type Milestone = z.infer<typeof milestoneSchema>;
export type Agent = z.infer<typeof agentSchema>;
export type AgentLogEntry = z.infer<typeof agentLogEntrySchema>;
export type Phase = z.infer<typeof phaseSchema>;
export type TrackerState = z.infer<typeof trackerStateSchema>;

export const createEmptyTracker = (overrides?: Partial<ProjectMeta>): TrackerState => ({
  meta: {
    schema_version: 1,
    version: 0,
    last_updated_at: new Date().toISOString(),
  },
  project: {
    name: overrides?.name ?? 'My Project',
    start_date: overrides?.start_date ?? '2026-01-01',
    target_date: overrides?.target_date ?? '2026-06-30',
    current_week: overrides?.current_week ?? 1,
    schedule_status: overrides?.schedule_status ?? 'on_track',
    overall_progress: overrides?.overall_progress ?? 0,
  },
  milestones: [],
  agents: [],
  agent_log: [],
  domains: [],
  schedule: {
    phases: [],
  },
});
