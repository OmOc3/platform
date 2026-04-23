import { app, BrowserWindow, dialog, ipcMain } from 'electron';
import chokidar, { type FSWatcher } from 'chokidar';
import { access, stat } from 'node:fs/promises';
import { constants as fsConstants } from 'node:fs';
import path from 'node:path';

import {
  ensureTrackerFile,
  readTracker,
  resolveTrackerPath,
  type TrackerState,
  writeTracker,
} from '@command-center/tracker-core/node';

import { readSettings, writeSettings } from './config.js';

let mainWindow: BrowserWindow | null = null;
let watcher: FSWatcher | null = null;
let currentProjectRoot: string | null = null;
let currentTrackerPath: string | null = null;
let lastWriteTime = 0;

app.setName('Command Center');
app.setAppUserModelId('com.codex.commandcenter');

const resolveAssetPath = (...segments: string[]) => path.join(app.getAppPath(), ...segments);

const fileExists = async (targetPath: string | null): Promise<boolean> => {
  if (!targetPath) return false;

  try {
    await access(targetPath, fsConstants.F_OK);
    return true;
  } catch {
    return false;
  }
};

const createWindow = () => {
  const iconPath = resolveAssetPath('build', 'icon.png');

  mainWindow = new BrowserWindow({
    width: 1440,
    height: 920,
    minWidth: 1220,
    minHeight: 780,
    backgroundColor: '#0A0A10',
    show: false,
    title: 'Command Center',
    icon: iconPath,
    webPreferences: {
      preload: path.join(__dirname, '../preload/index.js'),
      contextIsolation: true,
      nodeIntegration: false,
    },
  });

  mainWindow.once('ready-to-show', () => mainWindow?.show());

  if (process.env.VITE_DEV_SERVER_URL) {
    void mainWindow.loadURL(process.env.VITE_DEV_SERVER_URL);
  } else {
    void mainWindow.loadFile(path.join(__dirname, '../renderer/index.html'));
  }
};

const closeWatcher = async () => {
  if (watcher) {
    await watcher.close();
    watcher = null;
  }
};

const broadcastTrackerUpdate = async () => {
  if (!mainWindow || !currentProjectRoot) return;

  try {
    const state = await readTracker(currentProjectRoot);
    mainWindow.webContents.send('tracker:updated', state);
  } catch {
    // Ignore partial writes or invalid payloads until the next stable event.
  }
};

const startWatcher = async () => {
  await closeWatcher();

  if (!currentTrackerPath) {
    return;
  }

  watcher = chokidar.watch(currentTrackerPath, {
    ignoreInitial: true,
    awaitWriteFinish: {
      stabilityThreshold: 150,
      pollInterval: 50,
    },
  });

  watcher.on('change', async () => {
    if (Date.now() - lastWriteTime < 500) {
      return;
    }

    await broadcastTrackerUpdate();
  });

  watcher.on('error', (error) => {
    console.error('[command-center watcher]', error);
  });
};

const setProjectRoot = async (projectRoot: string | null) => {
  currentProjectRoot = projectRoot ? path.normalize(path.resolve(projectRoot)) : null;
  currentTrackerPath = currentProjectRoot ? resolveTrackerPath(currentProjectRoot) : null;
  await writeSettings({ projectRoot: currentProjectRoot });

  if (currentProjectRoot) {
    await ensureTrackerFile(currentProjectRoot);
    await startWatcher();
  } else {
    await closeWatcher();
  }
};

const trackerFileInfo = async () => {
  const exists = await fileExists(currentTrackerPath);

  if (!exists || !currentTrackerPath) {
    return {
      exists: false,
      size: 0,
      lastModified: null,
      watcherActive: Boolean(watcher),
      projectRoot: currentProjectRoot,
    };
  }

  const info = await stat(currentTrackerPath);

  return {
    exists: true,
    size: info.size,
    lastModified: info.mtime.toISOString(),
    watcherActive: Boolean(watcher),
    projectRoot: currentProjectRoot,
  };
};

app.whenReady().then(async () => {
  const settings = await readSettings();
  if (settings.projectRoot) {
    await setProjectRoot(settings.projectRoot);
  }

  createWindow();

  ipcMain.handle('config:getProjectRoot', async () => currentProjectRoot);
  ipcMain.handle('config:pickProjectRoot', async () => {
    const result = await dialog.showOpenDialog({
      properties: ['openDirectory'],
      title: 'Select project root',
    });

    if (result.canceled || result.filePaths.length === 0) {
      return currentProjectRoot;
    }

    await setProjectRoot(result.filePaths[0]);
    await broadcastTrackerUpdate();

    return currentProjectRoot;
  });

  ipcMain.handle('tracker:read', async () => {
    if (!currentProjectRoot) {
      return null;
    }

    return ensureTrackerFile(currentProjectRoot);
  });

  ipcMain.handle('tracker:write', async (_event, state: TrackerState, expectedVersion?: number) => {
    if (!currentProjectRoot) {
      return { success: false, error: 'Project root is not configured.' };
    }

    try {
      lastWriteTime = Date.now();
      const persisted = await writeTracker(currentProjectRoot, state, { expectedVersion });
      mainWindow?.webContents.send('tracker:updated', persisted);
      return { success: true, state: persisted };
    } catch (error) {
      return {
        success: false,
        error: error instanceof Error ? error.message : 'Unable to write tracker.',
      };
    }
  });

  ipcMain.handle('tracker:path', async () => currentTrackerPath);
  ipcMain.handle('tracker:fileInfo', trackerFileInfo);

  app.on('activate', () => {
    if (BrowserWindow.getAllWindows().length === 0) {
      createWindow();
    }
  });
});

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') {
    app.quit();
  }
});

app.on('before-quit', () => {
  void closeWatcher();
});
