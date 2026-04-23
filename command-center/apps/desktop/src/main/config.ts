import { app } from 'electron';
import { mkdir, readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';

export interface DesktopSettings {
  projectRoot: string | null;
}

const SETTINGS_FILENAME = 'command-center-settings.json';

const settingsPath = () => path.join(app.getPath('userData'), SETTINGS_FILENAME);

export const readSettings = async (): Promise<DesktopSettings> => {
  try {
    const raw = await readFile(settingsPath(), 'utf8');
    const parsed = JSON.parse(raw) as Partial<DesktopSettings>;
    return {
      projectRoot: parsed.projectRoot ?? null,
    };
  } catch {
    return { projectRoot: null };
  }
};

export const writeSettings = async (settings: DesktopSettings): Promise<void> => {
  const target = settingsPath();
  await mkdir(path.dirname(target), { recursive: true });
  await writeFile(target, `${JSON.stringify(settings, null, 2)}\n`, 'utf8');
};
