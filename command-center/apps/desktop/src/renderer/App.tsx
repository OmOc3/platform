import { useEffect } from 'react';

import { EmptyState } from './components/EmptyState';
import { ProjectRootGate } from './components/ProjectRootGate';
import { StatusBar } from './components/StatusBar';
import { TabBar } from './components/TabBar';
import { useAppStore } from './store';
import { AgentHubView } from './views/AgentHubView';
import { CalendarView } from './views/CalendarView';
import { SwimLaneView } from './views/SwimLaneView';
import { TaskBoardView } from './views/TaskBoardView';

const ActiveView = () => {
  const activeTab = useAppStore((state) => state.activeTab);

  switch (activeTab) {
    case 'swimlane':
      return <SwimLaneView />;
    case 'taskboard':
      return <TaskBoardView />;
    case 'agents':
      return <AgentHubView />;
    case 'calendar':
      return <CalendarView />;
    default:
      return null;
  }
};

export default function App() {
  const initialize = useAppStore((state) => state.initialize);
  const loading = useAppStore((state) => state.loading);
  const tracker = useAppStore((state) => state.tracker);
  const projectRoot = useAppStore((state) => state.projectRoot);
  const error = useAppStore((state) => state.error);
  const theme = useAppStore((state) => state.theme);
  const applyTrackerUpdate = useAppStore((state) => state.applyTrackerUpdate);

  useEffect(() => {
    void initialize();
  }, [initialize]);

  useEffect(() => {
    document.documentElement.dataset.theme = theme;
  }, [theme]);

  useEffect(() => {
    return window.api.tracker.onUpdated((nextTracker) => {
      applyTrackerUpdate(nextTracker);
    });
  }, [applyTrackerUpdate]);

  if (loading) {
    return (
      <div className="cc-loading-shell">
        <div className="cc-loading-card">Loading tracker…</div>
      </div>
    );
  }

  if (!projectRoot) {
    return <ProjectRootGate />;
  }

  if (!tracker) {
    return (
      <div className="cc-loading-shell">
        <EmptyState
          title="Tracker unavailable"
          description="The project root is set, but the tracker could not be loaded yet."
        />
      </div>
    );
  }

  return (
    <div className="cc-app-shell">
      <TabBar />
      {error ? <div className="cc-global-error">{error}</div> : null}
      <main className="cc-main">
        <ActiveView />
      </main>
      <StatusBar />
    </div>
  );
}
