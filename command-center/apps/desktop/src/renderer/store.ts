import type { TrackerState } from '@command-center/tracker-core';
import { create } from 'zustand';

export type AppTab = 'swimlane' | 'taskboard' | 'agents' | 'calendar';
export type ThemeMode = 'dark' | 'light';

interface AppState {
  initialized: boolean;
  loading: boolean;
  syncing: boolean;
  error: string | null;
  tracker: TrackerState | null;
  projectRoot: string | null;
  fileInfo: {
    exists: boolean;
    size: number;
    lastModified: string | null;
    watcherActive: boolean;
    projectRoot: string | null;
  };
  activeTab: AppTab;
  theme: ThemeMode;
  selectedMilestoneId: string | null;
  selectedTaskId: string | null;
  boardMilestoneId: string | null;
  calendarWeek: number;
  initialize: () => Promise<void>;
  syncFromDisk: () => Promise<void>;
  pickProjectRoot: () => Promise<void>;
  saveTracker: (nextTracker: TrackerState) => Promise<void>;
  updateTracker: (mutator: (draft: TrackerState) => void) => Promise<void>;
  applyTrackerUpdate: (tracker: TrackerState) => void;
  setActiveTab: (tab: AppTab) => void;
  setTheme: (theme: ThemeMode) => void;
  selectMilestone: (milestoneId: string | null) => void;
  selectTask: (taskId: string | null) => void;
  setBoardMilestoneId: (milestoneId: string | null) => void;
  setCalendarWeek: (week: number) => void;
  setError: (error: string | null) => void;
}

const defaultFileInfo = {
  exists: false,
  size: 0,
  lastModified: null,
  watcherActive: false,
  projectRoot: null,
};

const persistedTheme = (): ThemeMode => {
  if (typeof window === 'undefined') {
    return 'dark';
  }

  const value = window.localStorage.getItem('command-center-theme');
  return value === 'light' ? 'light' : 'dark';
};

export const useAppStore = create<AppState>((set, get) => ({
  initialized: false,
  loading: true,
  syncing: false,
  error: null,
  tracker: null,
  projectRoot: null,
  fileInfo: defaultFileInfo,
  activeTab: 'swimlane',
  theme: persistedTheme(),
  selectedMilestoneId: null,
  selectedTaskId: null,
  boardMilestoneId: null,
  calendarWeek: 1,
  initialize: async () => {
    if (get().initialized) {
      return;
    }

    await get().syncFromDisk();
    set({ initialized: true, loading: false });
  },
  syncFromDisk: async () => {
    set({ loading: true, error: null });

    try {
      const [projectRoot, tracker, fileInfo] = await Promise.all([
        window.api.config.getProjectRoot(),
        window.api.tracker.read(),
        window.api.tracker.getFileInfo(),
      ]);

      set((state) => ({
        projectRoot,
        tracker,
        fileInfo,
        boardMilestoneId: state.boardMilestoneId ?? tracker?.milestones[0]?.id ?? null,
        calendarWeek: tracker?.project.current_week ?? state.calendarWeek,
      }));
    } catch (error) {
      set({
        error: error instanceof Error ? error.message : 'Unable to load tracker state.',
      });
    } finally {
      set({ loading: false });
    }
  },
  pickProjectRoot: async () => {
    set({ syncing: true, error: null });

    try {
      await window.api.config.pickProjectRoot();
      await get().syncFromDisk();
    } catch (error) {
      set({
        error: error instanceof Error ? error.message : 'Unable to choose project root.',
      });
    } finally {
      set({ syncing: false });
    }
  },
  saveTracker: async (nextTracker) => {
    const current = get().tracker;

    if (!current) {
      return;
    }

    set({ syncing: true, error: null });

    try {
      const result = await window.api.tracker.write(nextTracker, current.meta.version);

      if (!result.success || !result.state) {
        throw new Error(result.error ?? 'Tracker write failed.');
      }

      const fileInfo = await window.api.tracker.getFileInfo();
      set({ tracker: result.state, fileInfo });
    } catch (error) {
      set({
        error: error instanceof Error ? error.message : 'Unable to save tracker.',
      });
    } finally {
      set({ syncing: false });
    }
  },
  updateTracker: async (mutator) => {
    const tracker = get().tracker;
    if (!tracker) return;

    const draft = structuredClone(tracker);
    mutator(draft);
    await get().saveTracker(draft);
  },
  applyTrackerUpdate: (tracker) =>
    set((state) => ({
      tracker,
      boardMilestoneId: state.boardMilestoneId ?? tracker.milestones[0]?.id ?? null,
      calendarWeek: tracker.project.current_week || state.calendarWeek,
      error: null,
    })),
  setActiveTab: (activeTab) => set({ activeTab }),
  setTheme: (theme) => {
    if (typeof window !== 'undefined') {
      window.localStorage.setItem('command-center-theme', theme);
    }
    set({ theme });
  },
  selectMilestone: (selectedMilestoneId) => set({ selectedMilestoneId }),
  selectTask: (selectedTaskId) => set({ selectedTaskId }),
  setBoardMilestoneId: (boardMilestoneId) => set({ boardMilestoneId }),
  setCalendarWeek: (calendarWeek) => set({ calendarWeek }),
  setError: (error) => set({ error }),
}));
