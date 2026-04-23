import { useEffect, useState } from 'react';

import type { ExecutionMode, TaskStatus } from '@command-center/tracker-core';

import { useAppStore } from '../store';

const statusOrder: TaskStatus[] = ['todo', 'in_progress', 'review', 'done', 'blocked'];
const executionModes: ExecutionMode[] = ['human', 'agent', 'pair'];

export function TaskDetailModal() {
  const tracker = useAppStore((state) => state.tracker);
  const selectedTaskId = useAppStore((state) => state.selectedTaskId);
  const selectTask = useAppStore((state) => state.selectTask);
  const updateTracker = useAppStore((state) => state.updateTracker);

  const located = tracker?.milestones
    .flatMap((milestone) => milestone.subtasks.map((task) => ({ task, milestone })))
    .find((entry) => entry.task.id === selectedTaskId);

  const [notes, setNotes] = useState('');
  const [assignee, setAssignee] = useState('');
  const [executionMode, setExecutionMode] = useState<ExecutionMode>('agent');

  useEffect(() => {
    setNotes(located?.task.notes ?? '');
    setAssignee(located?.task.assignee ?? '');
    setExecutionMode(located?.task.execution_mode ?? 'agent');
  }, [located]);

  if (!located) {
    return null;
  }

  const { task, milestone } = located;

  const updateStatus = async (status: TaskStatus) => {
    await updateTracker((draft) => {
      const nextMilestone = draft.milestones.find((item) => item.id === milestone.id);
      const nextTask = nextMilestone?.subtasks.find((item) => item.id === task.id);
      if (!nextTask) return;

      nextTask.status = status;
      nextTask.done = status === 'done';
      nextTask.blocked_by = status === 'blocked' ? 'manual' : null;
      nextTask.blocked_reason = status === 'blocked' ? nextTask.blocked_reason ?? 'Blocked from desktop board.' : null;

      if (status === 'done') {
        nextTask.completed_at = new Date().toISOString();
        nextTask.completed_by = 'operator';
      } else {
        nextTask.completed_at = null;
        nextTask.completed_by = null;
      }
    });
  };

  const saveDetails = async () => {
    await updateTracker((draft) => {
      const nextMilestone = draft.milestones.find((item) => item.id === milestone.id);
      const nextTask = nextMilestone?.subtasks.find((item) => item.id === task.id);
      if (!nextTask) return;
      nextTask.notes = notes.trim() === '' ? null : notes.trim();
      nextTask.assignee = assignee.trim() === '' ? null : assignee.trim();
      nextTask.execution_mode = executionMode;
    });
  };

  return (
    <div className="cc-modal-backdrop" onClick={() => selectTask(null)}>
      <div className="cc-modal" onClick={(event) => event.stopPropagation()}>
        <button type="button" className="cc-side-panel__close" onClick={() => selectTask(null)}>
          Close
        </button>

        <p className="cc-eyebrow">{milestone.title}</p>
        <h3 className="cc-modal__title">{task.label}</h3>
        <div className="cc-modal__meta">
          <span className="cc-inline-badge">{task.id}</span>
          <span className="cc-inline-badge">{task.priority}</span>
          <span className="cc-inline-badge">{task.status}</span>
        </div>

        <section className="cc-modal__section">
          <h4 className="cc-side-panel__heading">Status</h4>
          <div className="cc-segmented">
            {statusOrder.map((status) => (
              <button
                key={status}
                type="button"
                className={`cc-button ${task.status === status ? 'cc-button--primary' : 'cc-button--ghost'}`}
                onClick={() => void updateStatus(status)}
              >
                {status}
              </button>
            ))}
          </div>
        </section>

        <section className="cc-modal__section">
          <h4 className="cc-side-panel__heading">Execution</h4>
          <select className="cc-input" value={executionMode} onChange={(event) => setExecutionMode(event.target.value as ExecutionMode)}>
            {executionModes.map((mode) => (
              <option key={mode} value={mode}>
                {mode}
              </option>
            ))}
          </select>
        </section>

        <section className="cc-modal__section">
          <h4 className="cc-side-panel__heading">Assignee</h4>
          <input className="cc-input" value={assignee} onChange={(event) => setAssignee(event.target.value)} placeholder="agent or human" />
        </section>

        <section className="cc-modal__section">
          <h4 className="cc-side-panel__heading">Notes</h4>
          <textarea className="cc-textarea" value={notes} onChange={(event) => setNotes(event.target.value)} />
        </section>

        <section className="cc-modal__section">
          <h4 className="cc-side-panel__heading">Acceptance Criteria</h4>
          {task.acceptance_criteria.length > 0 ? (
            <ul className="cc-side-panel__list">
              {task.acceptance_criteria.map((item) => (
                <li key={item}>{item}</li>
              ))}
            </ul>
          ) : (
            <p>No acceptance criteria yet.</p>
          )}
        </section>

        <div className="cc-modal__actions">
          <button type="button" className="cc-button cc-button--primary" onClick={() => void saveDetails()}>
            Save
          </button>
        </div>
      </div>
    </div>
  );
}
