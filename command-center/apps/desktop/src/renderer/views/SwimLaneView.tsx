import { getMilestoneProgress } from '@command-center/tracker-core';

import { EmptyState } from '../components/EmptyState';
import { MetricCard } from '../components/MetricCard';
import { MilestonePanel } from '../components/MilestonePanel';
import { Panel } from '../components/Panel';
import { useAppStore } from '../store';

const fallbackColors = ['#f59e0b', '#22c55e', '#8286FF', '#ef4444', '#14B8A6', '#EC4899'];

export function SwimLaneView() {
  const tracker = useAppStore((state) => state.tracker);
  const selectMilestone = useAppStore((state) => state.selectMilestone);

  if (!tracker) {
    return null;
  }

  const domains =
    tracker.domains.length > 0
      ? tracker.domains
      : Array.from(new Set(tracker.milestones.map((milestone) => milestone.domain))).map((domain, index) => ({
          id: domain,
          label: domain,
          color: fallbackColors[index % fallbackColors.length],
        }));

  const weekCount = Math.max(
    tracker.project.current_week + 2,
    8,
    ...tracker.schedule.phases.map((phase) => phase.end_week),
    ...tracker.milestones.map((milestone) => milestone.week + 1),
  );

  return (
    <div className="cc-view-grid">
      <Panel
        eyebrow="Strategic timeline"
        title="Swim Lane"
        className="cc-view-grid__main"
        actions={<span className="cc-inline-badge">{tracker.milestones.length} milestones</span>}
      >
        {tracker.milestones.length === 0 ? (
          <EmptyState
            title="No milestones yet"
            description="Create milestones through the MCP server or hydrate the tracker to populate the swim lane."
          />
        ) : (
          <div className="cc-swimlane">
            <div className="cc-swimlane__header" style={{ gridTemplateColumns: `240px repeat(${weekCount}, minmax(5rem, 1fr))` }}>
              <div className="cc-swimlane__corner">Domain</div>
              {Array.from({ length: weekCount }).map((_, index) => (
                <div key={index + 1} className="cc-swimlane__week">
                  W{index + 1}
                </div>
              ))}
            </div>

            <div className="cc-swimlane__body">
              <div
                className="cc-swimlane__now-marker"
                style={{ left: `calc(240px + ((100% - 240px) * ${(tracker.project.current_week - 0.5) / weekCount}))` }}
              />
              {domains.map((domain) => (
                <div
                  key={domain.id}
                  className="cc-swimlane__row"
                  style={{ gridTemplateColumns: `240px repeat(${weekCount}, minmax(5rem, 1fr))` }}
                >
                  <div className="cc-swimlane__label">
                    <span className="cc-domain-dot" style={{ backgroundColor: domain.color }} />
                    <span>{domain.label}</span>
                  </div>

                  {Array.from({ length: weekCount }).map((_, index) => (
                    <div key={index + 1} className="cc-swimlane__cell" />
                  ))}

                  {tracker.milestones
                    .filter((milestone) => milestone.domain === domain.id)
                    .map((milestone) => {
                      const progress = getMilestoneProgress(milestone);

                      return (
                        <button
                          key={milestone.id}
                          type="button"
                          className="cc-swimlane__node"
                          style={{
                            gridColumn: `${milestone.week + 1} / span 1`,
                            borderColor: domain.color,
                            boxShadow: `0 0 0 1px ${domain.color}25`,
                          }}
                          onClick={() => selectMilestone(milestone.id)}
                        >
                          <span className="cc-swimlane__node-title">{milestone.title}</span>
                          <span className="cc-swimlane__node-meta">
                            {progress.done}/{progress.total} · drift {milestone.drift_days}d
                          </span>
                        </button>
                      );
                    })}
                </div>
              ))}
            </div>
          </div>
        )}
      </Panel>

      <aside className="cc-view-grid__side">
        <Panel eyebrow="Summary" title="Timeline Stats">
          <div className="cc-metric-grid">
            <MetricCard label="Milestones" value={tracker.milestones.length} />
            <MetricCard label="Current Week" value={tracker.project.current_week} />
            <MetricCard label="Phases" value={tracker.schedule.phases.length} />
            <MetricCard label="Domains" value={domains.length} />
          </div>
        </Panel>

        <MilestonePanel />
      </aside>
    </div>
  );
}
