import { getMilestoneProgress } from '@command-center/tracker-core';

import { useAppStore } from '../store';

export function MilestonePanel() {
  const tracker = useAppStore((state) => state.tracker);
  const selectedMilestoneId = useAppStore((state) => state.selectedMilestoneId);
  const selectMilestone = useAppStore((state) => state.selectMilestone);

  if (!tracker || !selectedMilestoneId) {
    return null;
  }

  const milestone = tracker.milestones.find((item) => item.id === selectedMilestoneId);

  if (!milestone) {
    return null;
  }

  const progress = getMilestoneProgress(milestone);

  return (
    <aside className="cc-side-panel">
      <button type="button" className="cc-side-panel__close" onClick={() => selectMilestone(null)}>
        Close
      </button>
      <p className="cc-eyebrow">{milestone.domain}</p>
      <h3 className="cc-side-panel__title">{milestone.title}</h3>
      <p className="cc-side-panel__description">
        Week {milestone.week} · {milestone.phase} · {progress.done}/{progress.total} done
      </p>

      <section className="cc-side-panel__section">
        <h4 className="cc-side-panel__heading">Dates</h4>
        <p>Planned: {milestone.planned_start ?? '—'} → {milestone.planned_end ?? '—'}</p>
        <p>Actual: {milestone.actual_start ?? '—'} → {milestone.actual_end ?? '—'}</p>
        <p>Drift: {milestone.drift_days} days</p>
      </section>

      <section className="cc-side-panel__section">
        <h4 className="cc-side-panel__heading">Dependencies</h4>
        {milestone.dependencies.length > 0 ? (
          <ul className="cc-side-panel__list">
            {milestone.dependencies.map((dependency) => (
              <li key={dependency}>{dependency}</li>
            ))}
          </ul>
        ) : (
          <p>No upstream milestones.</p>
        )}
      </section>

      <section className="cc-side-panel__section">
        <h4 className="cc-side-panel__heading">Tasks</h4>
        <ul className="cc-side-panel__list">
          {milestone.subtasks.map((task) => (
            <li key={task.id}>
              <span className="cc-inline-badge">{task.status}</span> {task.label}
            </li>
          ))}
        </ul>
      </section>

      <section className="cc-side-panel__section">
        <h4 className="cc-side-panel__heading">Notes</h4>
        {milestone.notes.length > 0 ? (
          <ul className="cc-side-panel__list">
            {milestone.notes.map((note) => (
              <li key={note}>{note}</li>
            ))}
          </ul>
        ) : (
          <p>No notes yet.</p>
        )}
      </section>
    </aside>
  );
}
