import type { DragEndEvent } from '@dnd-kit/core';
import { DndContext, PointerSensor, closestCorners, useDroppable, useSensor, useSensors } from '@dnd-kit/core';
import { SortableContext, useSortable, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import type { Subtask, TaskStatus } from '@command-center/tracker-core';
import { getMilestoneProgress } from '@command-center/tracker-core';

import { EmptyState } from '../components/EmptyState';
import { Panel } from '../components/Panel';
import { TaskDetailModal } from '../components/TaskDetailModal';
import { useAppStore } from '../store';

const columns: Array<{ id: TaskStatus; label: string }> = [
  { id: 'todo', label: 'TO DO' },
  { id: 'in_progress', label: 'IN PROGRESS' },
  { id: 'review', label: 'REVIEW' },
  { id: 'done', label: 'DONE' },
  { id: 'blocked', label: 'BLOCKED' },
];

function TaskCard({ task, onOpen }: { task: Subtask; onOpen: () => void }) {
  const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
    id: task.id,
  });

  return (
    <button
      type="button"
      ref={setNodeRef}
      className={`cc-task-card ${isDragging ? 'cc-task-card--dragging' : ''}`}
      style={{
        transform: CSS.Transform.toString(transform),
        transition,
      }}
      onClick={onOpen}
      {...attributes}
      {...listeners}
    >
      <div className="cc-task-card__meta">
        <span className="cc-inline-badge">{task.id}</span>
        <span className="cc-inline-badge">{task.priority}</span>
      </div>
      <p className="cc-task-card__title">{task.label}</p>
      <p className="cc-task-card__subtle">
        {task.assignee ?? 'unassigned'} · {task.execution_mode}
      </p>
      {task.blocked_reason ? <p className="cc-task-card__blocker">{task.blocked_reason}</p> : null}
    </button>
  );
}

function TaskColumn({
  columnId,
  label,
  tasks,
  onOpen,
}: {
  columnId: TaskStatus;
  label: string;
  tasks: Subtask[];
  onOpen: (taskId: string) => void;
}) {
  const { setNodeRef, isOver } = useDroppable({ id: `column:${columnId}` });

  return (
    <div className={`cc-board-column ${isOver ? 'cc-board-column--over' : ''}`}>
      <div className="cc-board-column__header">
        <span>{label}</span>
        <span className="cc-inline-badge">{tasks.length}</span>
      </div>
      <div ref={setNodeRef} className="cc-board-column__body">
        <SortableContext items={tasks.map((task) => task.id)} strategy={verticalListSortingStrategy}>
          {tasks.map((task) => (
            <TaskCard key={task.id} task={task} onOpen={() => onOpen(task.id)} />
          ))}
        </SortableContext>
      </div>
    </div>
  );
}

export function TaskBoardView() {
  const tracker = useAppStore((state) => state.tracker);
  const boardMilestoneId = useAppStore((state) => state.boardMilestoneId);
  const setBoardMilestoneId = useAppStore((state) => state.setBoardMilestoneId);
  const updateTracker = useAppStore((state) => state.updateTracker);
  const selectTask = useAppStore((state) => state.selectTask);

  const sensors = useSensors(useSensor(PointerSensor, { activationConstraint: { distance: 6 } }));

  if (!tracker) {
    return null;
  }

  if (tracker.milestones.length === 0) {
    return (
      <Panel eyebrow="Kanban" title="Task Board">
        <EmptyState
          title="No milestone tasks yet"
          description="Create milestones and subtasks through MCP to populate the board."
        />
      </Panel>
    );
  }

  const currentMilestone =
    tracker.milestones.find((milestone) => milestone.id === boardMilestoneId) ?? tracker.milestones[0];
  const progress = getMilestoneProgress(currentMilestone);

  const handleDragEnd = async (event: DragEndEvent) => {
    if (!event.over) return;

    const activeId = String(event.active.id);
    const overId = String(event.over.id);
    const activeTask = currentMilestone.subtasks.find((task) => task.id === activeId);
    if (!activeTask) return;

    let targetStatus: TaskStatus | null = null;

    if (overId.startsWith('column:')) {
      targetStatus = overId.replace('column:', '') as TaskStatus;
    } else {
      const overTask = currentMilestone.subtasks.find((task) => task.id === overId);
      targetStatus = overTask?.status ?? null;
    }

    if (!targetStatus || targetStatus === activeTask.status) {
      return;
    }

    await updateTracker((draft) => {
      const nextMilestone = draft.milestones.find((milestone) => milestone.id === currentMilestone.id);
      const nextTask = nextMilestone?.subtasks.find((task) => task.id === activeId);
      if (!nextTask) return;

      nextTask.status = targetStatus!;
      nextTask.done = targetStatus === 'done';
      if (targetStatus === 'done') {
        nextTask.completed_at = new Date().toISOString();
        nextTask.completed_by = 'operator';
        nextTask.blocked_by = null;
        nextTask.blocked_reason = null;
      } else if (targetStatus === 'blocked') {
        nextTask.completed_at = null;
        nextTask.completed_by = null;
        nextTask.blocked_by = 'manual';
        nextTask.blocked_reason = nextTask.blocked_reason ?? 'Blocked from desktop board.';
      } else {
        nextTask.completed_at = null;
        nextTask.completed_by = null;
        nextTask.blocked_by = null;
        nextTask.blocked_reason = null;
      }
    });
  };

  return (
    <Panel
      eyebrow="Tactical execution"
      title="Task Board"
      actions={<span className="cc-inline-badge">{progress.done}/{progress.total} done</span>}
    >
      <div className="cc-board-toolbar">
        <div className="cc-chip-row">
          {tracker.milestones.map((milestone) => (
            <button
              key={milestone.id}
              type="button"
              className={`cc-chip ${milestone.id === currentMilestone.id ? 'cc-chip--active' : ''}`}
              onClick={() => setBoardMilestoneId(milestone.id)}
            >
              {milestone.title}
            </button>
          ))}
        </div>
      </div>

      <DndContext sensors={sensors} collisionDetection={closestCorners} onDragEnd={(event) => void handleDragEnd(event)}>
        <div className="cc-board-grid">
          {columns.map((column) => (
            <TaskColumn
              key={column.id}
              columnId={column.id}
              label={column.label}
              tasks={currentMilestone.subtasks.filter((task) => task.status === column.id)}
              onOpen={selectTask}
            />
          ))}
        </div>
      </DndContext>

      <TaskDetailModal />
    </Panel>
  );
}
