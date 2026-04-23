import { Panel } from '../components/Panel';
import { useAppStore } from '../store';

const startOfWeek = (startDate: string, weekNumber: number) => {
  const date = new Date(`${startDate}T00:00:00`);
  date.setDate(date.getDate() + (weekNumber - 1) * 7);
  return date;
};

const formatDay = (date: Date) =>
  date.toLocaleDateString(undefined, {
    month: 'short',
    day: 'numeric',
  });

export function CalendarView() {
  const tracker = useAppStore((state) => state.tracker);
  const calendarWeek = useAppStore((state) => state.calendarWeek);
  const setCalendarWeek = useAppStore((state) => state.setCalendarWeek);

  if (!tracker) {
    return null;
  }

  const weekStart = startOfWeek(tracker.project.start_date, calendarWeek);
  const days = Array.from({ length: 7 }).map((_, index) => {
    const date = new Date(weekStart);
    date.setDate(weekStart.getDate() + index);
    return date;
  });

  const completedTasks = tracker.milestones.flatMap((milestone) =>
    milestone.subtasks
      .filter((task) => task.completed_at)
      .map((task) => ({ task, milestone })),
  );

  return (
    <Panel
      eyebrow="History"
      title="Calendar"
      actions={
        <div className="cc-chip-row">
          <button type="button" className="cc-button cc-button--ghost" onClick={() => setCalendarWeek(Math.max(1, calendarWeek - 1))}>
            Prev
          </button>
          <span className="cc-inline-badge">Week {calendarWeek}</span>
          <button type="button" className="cc-button cc-button--ghost" onClick={() => setCalendarWeek(calendarWeek + 1)}>
            Next
          </button>
        </div>
      }
    >
      <div className="cc-calendar-grid">
        {days.map((day) => {
          const dayKey = day.toISOString().slice(0, 10);
          const dayItems = completedTasks.filter(({ task }) => task.completed_at?.slice(0, 10) === dayKey);
          const isToday = new Date().toISOString().slice(0, 10) === dayKey;

          return (
            <section key={dayKey} className={`cc-calendar-day ${isToday ? 'cc-calendar-day--today' : ''}`}>
              <header className="cc-calendar-day__header">
                <span>{day.toLocaleDateString(undefined, { weekday: 'short' })}</span>
                <strong>{formatDay(day)}</strong>
              </header>
              <div className="cc-calendar-day__body">
                {dayItems.map(({ task, milestone }) => (
                  <article key={task.id} className="cc-calendar-chip">
                    <strong>{task.label}</strong>
                    <span>{milestone.title}</span>
                  </article>
                ))}
              </div>
            </section>
          );
        })}
      </div>
    </Panel>
  );
}
