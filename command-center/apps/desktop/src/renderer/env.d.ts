import type { TrackerState } from '@command-center/tracker-core';

interface TrackerFileInfo {
  exists: boolean;
  size: number;
  lastModified: string | null;
  watcherActive: boolean;
  projectRoot: string | null;
}

interface TrackerAPI {
  read(): Promise<TrackerState | null>;
  write(
    state: TrackerState,
    expectedVersion?: number,
  ): Promise<{ success: boolean; state?: TrackerState; error?: string }>;
  getPath(): Promise<string | null>;
  getFileInfo(): Promise<TrackerFileInfo>;
  onUpdated(callback: (state: TrackerState) => void): () => void;
}

interface ConfigAPI {
  getProjectRoot(): Promise<string | null>;
  pickProjectRoot(): Promise<string | null>;
}

declare global {
  interface Window {
    api: {
      platform: string;
      tracker: TrackerAPI;
      config: ConfigAPI;
    };
  }
}

export {};
