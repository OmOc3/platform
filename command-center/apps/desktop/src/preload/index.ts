import { contextBridge, ipcRenderer } from 'electron';

import type { TrackerState } from '@command-center/tracker-core';

contextBridge.exposeInMainWorld('api', {
  platform: process.platform,
  tracker: {
    read: () => ipcRenderer.invoke('tracker:read') as Promise<TrackerState | null>,
    write: (state: TrackerState, expectedVersion?: number) =>
      ipcRenderer.invoke('tracker:write', state, expectedVersion) as Promise<{
        success: boolean;
        state?: TrackerState;
        error?: string;
      }>,
    getPath: () => ipcRenderer.invoke('tracker:path') as Promise<string | null>,
    getFileInfo: () =>
      ipcRenderer.invoke('tracker:fileInfo') as Promise<{
        exists: boolean;
        size: number;
        lastModified: string | null;
        watcherActive: boolean;
        projectRoot: string | null;
      }>,
    onUpdated: (callback: (state: TrackerState) => void) => {
      const handler = (_event: Electron.IpcRendererEvent, state: TrackerState) => callback(state);
      ipcRenderer.on('tracker:updated', handler);
      return () => ipcRenderer.removeListener('tracker:updated', handler);
    },
  },
  config: {
    getProjectRoot: () => ipcRenderer.invoke('config:getProjectRoot') as Promise<string | null>,
    pickProjectRoot: () => ipcRenderer.invoke('config:pickProjectRoot') as Promise<string | null>,
  },
});
