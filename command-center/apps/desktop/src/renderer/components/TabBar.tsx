import { useAppStore, type AppTab, type ThemeMode } from '../store';

const tabs: Array<{ id: AppTab; label: string }> = [
  { id: 'swimlane', label: 'Swim Lane' },
  { id: 'taskboard', label: 'Task Board' },
  { id: 'agents', label: 'Agent Hub' },
  { id: 'calendar', label: 'Calendar' },
];

export function TabBar() {
  const activeTab = useAppStore((state) => state.activeTab);
  const setActiveTab = useAppStore((state) => state.setActiveTab);
  const theme = useAppStore((state) => state.theme);
  const setTheme = useAppStore((state) => state.setTheme);

  const toggleTheme = () => {
    const nextTheme: ThemeMode = theme === 'dark' ? 'light' : 'dark';
    setTheme(nextTheme);
  };

  return (
    <header className="cc-tabbar">
      <div className="cc-tabbar__tabs">
        {tabs.map((tab) => (
          <button
            key={tab.id}
            type="button"
            className={`cc-tab ${activeTab === tab.id ? 'cc-tab--active' : ''}`}
            onClick={() => setActiveTab(tab.id)}
          >
            {tab.label}
          </button>
        ))}
      </div>
      <button type="button" className="cc-button cc-button--ghost" onClick={toggleTheme}>
        {theme === 'dark' ? 'Light' : 'Dark'}
      </button>
    </header>
  );
}
