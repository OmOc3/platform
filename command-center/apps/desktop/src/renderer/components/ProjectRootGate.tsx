import { useAppStore } from '../store';

export function ProjectRootGate() {
  const pickProjectRoot = useAppStore((state) => state.pickProjectRoot);
  const syncing = useAppStore((state) => state.syncing);
  const error = useAppStore((state) => state.error);

  return (
    <div className="cc-gate">
      <div className="cc-gate__card">
        <p className="cc-eyebrow">Command Center</p>
        <h1 className="cc-gate__title">Select a project root to start the tracker.</h1>
        <p className="cc-gate__description">
          The desktop app watches a single <code>project-tracker.json</code> file in the chosen project directory.
        </p>
        <button className="cc-button cc-button--primary" onClick={() => void pickProjectRoot()} disabled={syncing}>
          {syncing ? 'Opening…' : 'Choose Project Root'}
        </button>
        {error ? <p className="cc-error-text">{error}</p> : null}
      </div>
    </div>
  );
}
