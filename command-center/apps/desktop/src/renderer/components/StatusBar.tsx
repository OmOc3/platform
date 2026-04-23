import { getProgressCounts } from '@command-center/tracker-core';

import { useAppStore } from '../store';

export function StatusBar() {
  const tracker = useAppStore((state) => state.tracker);
  const fileInfo = useAppStore((state) => state.fileInfo);
  const syncing = useAppStore((state) => state.syncing);

  if (!tracker) {
    return null;
  }

  const counts = getProgressCounts(tracker);

  return (
    <footer className="cc-status-bar">
      <div className="cc-status-bar__group">
        <span className="cc-status-pill">{tracker.project.schedule_status}</span>
        <span>Week {tracker.project.current_week}</span>
        <span>{(tracker.project.overall_progress * 100).toFixed(1)}%</span>
        <span>{counts.done}/{counts.total} done</span>
        <span>{counts.blocked} blocked</span>
      </div>
      <div className="cc-status-bar__group">
        <span>{fileInfo.projectRoot ?? 'No project root'}</span>
        <span>{fileInfo.watcherActive ? 'Watcher active' : 'Watcher inactive'}</span>
        <span>v{tracker.meta.version}</span>
        <span>{syncing ? 'Syncing…' : 'Synced'}</span>
      </div>
    </footer>
  );
}
