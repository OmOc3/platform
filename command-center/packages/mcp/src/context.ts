import { getMilestoneProgress, getProgressCounts, selectCurrentWeek, type Milestone, type Subtask, type TrackerState } from '@command-center/tracker-core';

import { formatDate } from './helpers.js';

const statusLabel: Record<Subtask['status'], string> = {
  todo: 'todo',
  in_progress: 'in progress',
  review: 'review',
  done: 'done',
  blocked: 'blocked',
};

export const buildProjectStatus = (state: TrackerState): string => {
  const counts = getProgressCounts(state);
  const phases = state.schedule.phases.map((phase) => `- ${phase.title}: weeks ${phase.start_week}-${phase.end_week}`);

  return [
    `# ${state.project.name}`,
    '',
    `- Start date: ${state.project.start_date}`,
    `- Target date: ${state.project.target_date}`,
    `- Current week: ${selectCurrentWeek(state)}`,
    `- Schedule status: ${state.project.schedule_status}`,
    `- Overall progress: ${(state.project.overall_progress * 100).toFixed(1)}%`,
    '',
    '## Task counts',
    `- Total: ${counts.total}`,
    `- Done: ${counts.done}`,
    `- In progress: ${counts.inProgress}`,
    `- Review: ${counts.review}`,
    `- Blocked: ${counts.blocked}`,
    '',
    '## Milestones',
    ...state.milestones.map((milestone) => {
      const progress = getMilestoneProgress(milestone);
      return `- ${milestone.title} (${milestone.id}) · week ${milestone.week} · ${progress.done}/${progress.total} done · drift ${milestone.drift_days}d`;
    }),
    '',
    '## Schedule phases',
    ...(phases.length > 0 ? phases : ['- No phases configured']),
  ].join('\n');
};

export const buildMilestoneOverview = (milestone: Milestone, state: TrackerState): string => {
  const progress = getMilestoneProgress(milestone);
  const dependencyLines = milestone.dependencies.map((dependencyId) => {
    const dependency = state.milestones.find((item) => item.id === dependencyId);
    if (!dependency) return `- ${dependencyId} (missing)`;
    const depProgress = getMilestoneProgress(dependency);
    return `- ${dependency.title} (${dependency.id}) · ${depProgress.done}/${depProgress.total} done`;
  });

  return [
    `# ${milestone.title}`,
    '',
    `- ID: ${milestone.id}`,
    `- Domain: ${milestone.domain}`,
    `- Phase: ${milestone.phase}`,
    `- Week: ${milestone.week}`,
    `- Planned: ${formatDate(milestone.planned_start)} → ${formatDate(milestone.planned_end)}`,
    `- Actual: ${formatDate(milestone.actual_start)} → ${formatDate(milestone.actual_end)}`,
    `- Drift: ${milestone.drift_days} days`,
    `- Progress: ${progress.done}/${progress.total} (${progress.percent}%)`,
    '',
    '## Tasks',
    ...(milestone.subtasks.length > 0
      ? milestone.subtasks.map((task) => `- [${statusLabel[task.status]}] ${task.label} (${task.id}) · ${task.priority}`)
      : ['- No tasks yet']),
    '',
    '## Dependencies',
    ...(dependencyLines.length > 0 ? dependencyLines : ['- No milestone dependencies']),
    '',
    '## Notes',
    ...(milestone.notes.length > 0 ? milestone.notes.map((note) => `- ${note}`) : ['- No notes']),
  ].join('\n');
};

export const buildTaskContext = (state: TrackerState, subtask: Subtask, milestone: Milestone): string => {
  const dependencyLines = subtask.depends_on.map((dependencyId) => {
    const dependency = state.milestones.flatMap((item) => item.subtasks).find((task) => task.id === dependencyId);
    return dependency
      ? `- ${dependency.label} (${dependency.id}) · ${statusLabel[dependency.status]}`
      : `- ${dependencyId} (missing)`;
  });

  const upstreamMilestones = milestone.dependencies.map((dependencyId) => {
    const dependency = state.milestones.find((item) => item.id === dependencyId);
    return dependency ? `- ${dependency.title} (${dependency.id})` : `- ${dependencyId} (missing)`;
  });

  return [
    `# Task Context: ${subtask.label}`,
    '',
    `- Task ID: ${subtask.id}`,
    `- Status: ${statusLabel[subtask.status]}`,
    `- Priority: ${subtask.priority}`,
    `- Execution mode: ${subtask.execution_mode}`,
    `- Assignee: ${subtask.assignee ?? 'unassigned'}`,
    `- Milestone: ${milestone.title} (${milestone.id})`,
    `- Domain: ${milestone.domain}`,
    `- Phase: ${milestone.phase}`,
    '',
    '## Prompt',
    subtask.prompt ?? 'No prompt defined.',
    '',
    '## Acceptance Criteria',
    ...(subtask.acceptance_criteria.length > 0 ? subtask.acceptance_criteria.map((item) => `- ${item}`) : ['- None']),
    '',
    '## Constraints',
    ...(subtask.constraints.length > 0 ? subtask.constraints.map((item) => `- ${item}`) : ['- None']),
    '',
    '## Context Files',
    ...(subtask.context_files.length > 0 ? subtask.context_files.map((item) => `- ${item}`) : ['- None']),
    '',
    '## Reference Docs',
    ...(subtask.reference_docs.length > 0 ? subtask.reference_docs.map((item) => `- ${item}`) : ['- None']),
    '',
    '## Task Dependencies',
    ...(dependencyLines.length > 0 ? dependencyLines : ['- No task dependencies']),
    '',
    '## Upstream Milestones',
    ...(upstreamMilestones.length > 0 ? upstreamMilestones : ['- No milestone dependencies']),
    '',
    '## Notes',
    subtask.notes ?? 'No notes.',
    '',
    '## Blockers',
    subtask.blocked_reason ? `- ${subtask.blocked_reason}` : '- Not blocked',
  ].join('\n');
};

export const buildTaskSummary = (_state: TrackerState, subtask: Subtask, milestone: Milestone): string => [
  `# ${subtask.label}`,
  '',
  `- Task ID: ${subtask.id}`,
  `- Milestone: ${milestone.title}`,
  `- Status: ${statusLabel[subtask.status]}`,
  `- Priority: ${subtask.priority}`,
  `- Assignee: ${subtask.assignee ?? 'unassigned'}`,
  `- Prompt file: ${subtask.builder_prompt ?? 'none'}`,
  `- Acceptance criteria: ${subtask.acceptance_criteria.length}`,
  `- Context files: ${subtask.context_files.length}`,
].join('\n');
