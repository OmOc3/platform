import { getProgressCounts } from '@command-center/tracker-core';
import { useState } from 'react';

import { EmptyState } from '../components/EmptyState';
import { MetricCard } from '../components/MetricCard';
import { Panel } from '../components/Panel';
import { useAppStore } from '../store';

export function AgentHubView() {
  const tracker = useAppStore((state) => state.tracker);
  const fileInfo = useAppStore((state) => state.fileInfo);
  const [copied, setCopied] = useState(false);

  if (!tracker) {
    return null;
  }

  const counts = getProgressCounts(tracker);
  const currentPhase = tracker.schedule.phases.find(
    (phase) => tracker.project.current_week >= phase.start_week && tracker.project.current_week <= phase.end_week,
  );
  const contextInjection = [
    `PROJECT: ${tracker.project.name}`,
    `WEEK ${tracker.project.current_week}`,
    `PHASE: ${currentPhase?.title ?? 'Unscheduled'}`,
    `PROGRESS: ${(tracker.project.overall_progress * 100).toFixed(1)}% (${counts.done}/${counts.total})`,
    `SCHEDULE: ${tracker.project.schedule_status}`,
    `BLOCKED: ${counts.blocked}`,
  ].join(', ');

  const copyContext = async () => {
    await navigator.clipboard.writeText(contextInjection);
    setCopied(true);
    window.setTimeout(() => setCopied(false), 2000);
  };

  return (
    <div className="cc-view-grid">
      <aside className="cc-view-grid__side">
        <Panel eyebrow="Health" title="Overview">
          <div className="cc-metric-grid">
            <MetricCard label="Agents" value={tracker.agents.length} />
            <MetricCard label="Logs" value={tracker.agent_log.length} />
            <MetricCard label="Blocked" value={counts.blocked} />
            <MetricCard label="Version" value={tracker.meta.version} />
          </div>
        </Panel>

        <Panel eyebrow="Tracker" title="File Watch">
          <div className="cc-stack-list">
            <div className="cc-stack-item">
              <span>Path</span>
              <strong>{fileInfo.projectRoot ?? 'No project root'}</strong>
            </div>
            <div className="cc-stack-item">
              <span>Watcher</span>
              <strong>{fileInfo.watcherActive ? 'Active' : 'Inactive'}</strong>
            </div>
            <div className="cc-stack-item">
              <span>Last Modified</span>
              <strong>{fileInfo.lastModified ?? '—'}</strong>
            </div>
          </div>
        </Panel>

        <Panel
          eyebrow="Context Injection"
          title="Operator Prompt"
          actions={
            <button type="button" className="cc-button cc-button--ghost" onClick={() => void copyContext()}>
              {copied ? 'Copied' : 'Copy'}
            </button>
          }
        >
          <pre className="cc-code-block">{contextInjection}</pre>
        </Panel>
      </aside>

      <Panel eyebrow="Real-time activity" title="Agent Feed" className="cc-view-grid__main">
        {tracker.agent_log.length === 0 ? (
          <EmptyState title="No agent activity yet" description="The feed will populate as MCP tools are called." />
        ) : (
          <div className="cc-feed">
            {tracker.agent_log
              .slice()
              .sort((a, b) => b.timestamp.localeCompare(a.timestamp))
              .map((entry) => (
                <article key={entry.id} className="cc-feed__item">
                  <div className="cc-feed__header">
                    <strong>{entry.agent_id}</strong>
                    <span className="cc-inline-badge">{entry.action}</span>
                    <span className="cc-feed__time">{entry.timestamp}</span>
                  </div>
                  <p className="cc-feed__description">{entry.description}</p>
                  <p className="cc-feed__meta">
                    {entry.target_type}: {entry.target_id} · {entry.tags.join(', ')}
                  </p>
                </article>
              ))}
          </div>
        )}
      </Panel>
    </div>
  );
}
