import type { Milestone, Subtask, TrackerState, ScheduleStatus } from './schema.js';

const MS_PER_DAY = 1000 * 60 * 60 * 24;

export interface ProgressCounts {
  total: number;
  done: number;
  inProgress: number;
  blocked: number;
  review: number;
}

export const getAllTasks = (state: TrackerState): Subtask[] =>
  state.milestones.flatMap((milestone) => milestone.subtasks);

export const getProgressCounts = (state: TrackerState): ProgressCounts => {
  const tasks = getAllTasks(state);

  return tasks.reduce<ProgressCounts>(
    (acc, task) => {
      acc.total += 1;

      if (task.status === 'done') acc.done += 1;
      if (task.status === 'in_progress') acc.inProgress += 1;
      if (task.status === 'blocked') acc.blocked += 1;
      if (task.status === 'review') acc.review += 1;

      return acc;
    },
    { total: 0, done: 0, inProgress: 0, blocked: 0, review: 0 },
  );
};

export const selectOverallProgress = (state: TrackerState): number => {
  const counts = getProgressCounts(state);

  if (counts.total === 0) {
    return 0;
  }

  return Number((counts.done / counts.total).toFixed(4));
};

export const selectCurrentWeek = (state: TrackerState, now = new Date()): number => {
  const start = new Date(`${state.project.start_date}T00:00:00`);
  const diffDays = Math.max(0, Math.floor((now.getTime() - start.getTime()) / MS_PER_DAY));

  return Math.floor(diffDays / 7) + 1;
};

export const selectScheduleStatus = (state: TrackerState): ScheduleStatus => {
  if (state.milestones.length === 0) {
    return 'on_track';
  }

  const drifts = state.milestones.map((milestone) => milestone.drift_days);
  const maxDrift = Math.max(...drifts);
  const minDrift = Math.min(...drifts);

  if (maxDrift > 3) {
    return 'behind';
  }

  if (minDrift < -3) {
    return 'ahead';
  }

  return 'on_track';
};

export const getMilestoneProgress = (milestone: Milestone) => {
  const total = milestone.subtasks.length;
  const done = milestone.subtasks.filter((task) => task.status === 'done').length;

  return {
    total,
    done,
    percent: total === 0 ? 0 : Number(((done / total) * 100).toFixed(1)),
  };
};

export const milestoneIsComplete = (milestone: Milestone): boolean =>
  milestone.subtasks.length > 0 && milestone.subtasks.every((task) => task.status === 'done');

export const calculateDriftDays = (plannedStart: string | null, actualStart: string | null): number => {
  if (!plannedStart || !actualStart) {
    return 0;
  }

  const planned = new Date(`${plannedStart}T00:00:00`);
  const actual = new Date(`${actualStart}T00:00:00`);

  return Math.round((actual.getTime() - planned.getTime()) / MS_PER_DAY);
};

export const recomputeDerivedFields = (state: TrackerState): TrackerState => ({
  ...state,
  project: {
    ...state.project,
    current_week: selectCurrentWeek(state),
    overall_progress: selectOverallProgress(state),
    schedule_status: selectScheduleStatus(state),
  },
});
