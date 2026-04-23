import { access, mkdir, readFile, rename, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { constants as fsConstants } from 'node:fs';

import { createEmptyTracker, trackerStateSchema, type TrackerState } from './schema.js';
import { recomputeDerivedFields } from './selectors.js';

export class TrackerError extends Error {}

export class TrackerVersionConflictError extends TrackerError {
  constructor(expectedVersion: number, actualVersion: number) {
    super(`Tracker version conflict. Expected version ${expectedVersion}, found ${actualVersion}.`);
    this.name = 'TrackerVersionConflictError';
  }
}

export interface ResolveProjectRootOptions {
  projectRoot?: string;
  env?: Record<string, string | undefined>;
  cwd?: string;
  envFilePath?: string;
}

export interface WriteTrackerOptions {
  expectedVersion?: number;
}

const TRACKER_FILENAME = 'project-tracker.json';

const fileExists = async (targetPath: string): Promise<boolean> => {
  try {
    await access(targetPath, fsConstants.F_OK);
    return true;
  } catch {
    return false;
  }
};

const readEnvProjectRoot = async (envFilePath: string): Promise<string | null> => {
  try {
    const text = await readFile(envFilePath, 'utf8');
    const match = text.match(/^PROJECT_ROOT=(.+)$/m);
    return match?.[1]?.trim() ?? null;
  } catch {
    return null;
  }
};

export const resolveProjectRoot = async (options: ResolveProjectRootOptions = {}): Promise<string> => {
  const env = options.env ?? process.env;
  const cwd = options.cwd ?? process.cwd();
  const envFilePath = options.envFilePath ?? path.join(cwd, '.env');

  const rawRoot =
    options.projectRoot ??
    env.PROJECT_ROOT ??
    (await readEnvProjectRoot(envFilePath));

  if (!rawRoot) {
    throw new TrackerError('PROJECT_ROOT is not set.');
  }

  return path.normalize(path.resolve(cwd, rawRoot));
};

export const resolveTrackerPath = (projectRoot: string): string =>
  path.join(projectRoot, TRACKER_FILENAME);

export const ensureTrackerFile = async (projectRoot: string): Promise<TrackerState> => {
  const trackerPath = resolveTrackerPath(projectRoot);

  if (await fileExists(trackerPath)) {
    return readTracker(projectRoot);
  }

  await mkdir(projectRoot, { recursive: true });
  const initial = recomputeDerivedFields(createEmptyTracker());
  await persistTracker(trackerPath, initial);

  return initial;
};

export const readTracker = async (projectRoot: string): Promise<TrackerState> => {
  const trackerPath = resolveTrackerPath(projectRoot);
  const raw = await readFile(trackerPath, 'utf8');
  const parsed = JSON.parse(raw);

  return trackerStateSchema.parse(parsed);
};

const persistTracker = async (trackerPath: string, state: TrackerState): Promise<void> => {
  const directory = path.dirname(trackerPath);
  const tempPath = path.join(directory, `${path.basename(trackerPath)}.tmp`);
  const payload = `${JSON.stringify(state, null, 2)}\n`;

  await mkdir(directory, { recursive: true });
  await writeFile(tempPath, payload, 'utf8');
  await rename(tempPath, trackerPath);
};

export const writeTracker = async (
  projectRoot: string,
  nextState: TrackerState,
  options: WriteTrackerOptions = {},
): Promise<TrackerState> => {
  const trackerPath = resolveTrackerPath(projectRoot);
  const currentState = (await fileExists(trackerPath))
    ? await readTracker(projectRoot)
    : createEmptyTracker();

  if (
    typeof options.expectedVersion === 'number' &&
    currentState.meta.version !== options.expectedVersion
  ) {
    throw new TrackerVersionConflictError(options.expectedVersion, currentState.meta.version);
  }

  const derived = recomputeDerivedFields(nextState);
  const persisted = trackerStateSchema.parse({
    ...derived,
    meta: {
      schema_version: 1,
      version: currentState.meta.version + 1,
      last_updated_at: new Date().toISOString(),
    },
  });

  await persistTracker(trackerPath, persisted);

  return persisted;
};

export const updateTracker = async (
  projectRoot: string,
  updater: (current: TrackerState) => TrackerState | Promise<TrackerState>,
  options: WriteTrackerOptions = {},
): Promise<TrackerState> => {
  const current = await ensureTrackerFile(projectRoot);
  const next = await updater(current);

  return writeTracker(projectRoot, next, {
    expectedVersion: options.expectedVersion ?? current.meta.version,
  });
};
