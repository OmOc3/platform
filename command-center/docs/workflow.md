# Command Center Workflow

## Core loop

1. The operator opens the desktop app and selects a project root.
2. The desktop app watches `project-tracker.json` and renders the current state.
3. MCP calls or CLI commands mutate the tracker with optimistic version checks.
4. The watcher broadcasts file changes back into the desktop UI.

This keeps a single tracker file as the source of truth without silent overwrite races.

## Roles

- Operator: approves, rejects, resets, and curates milestone/task state
- Planner agent: decomposes milestones into concrete subtasks and acceptance criteria
- Builder agent: executes a single subtask and logs status updates
- Reviewer agent: validates completion, quality, and blockers

Prompt starters for these roles live in `templates/agent-role-prompts.md`.

## Project root setup

### Desktop

No environment variable is required.
Choose the project folder from the desktop UI when prompted.

### MCP / CLI

Set one of:

- `PROJECT_ROOT` environment variable
- `.env` file beside the MCP process with `PROJECT_ROOT=...`

## Recommended daily sequence

1. Load the tracker in the desktop app.
2. Review the swim lane for week/phase drift.
3. Pick a milestone in the task board and assign execution mode plus assignee.
4. Use MCP tools to start, enrich, block, complete, approve, or reject tasks.
5. Use the agent hub to copy the current context injection line into your orchestrator prompt.
6. Review the calendar for completed work and late milestones.

## Safety model

- Every persisted write increments `meta.version`.
- Stale writes are rejected with a version conflict error.
- Writes are persisted through a temp file plus rename.
- Operator-only actions require `actor_role=operator`.

## Current implementation boundary

The current build intentionally matches the original 24-tool count.
That means:

- milestone/task/agent operations are covered end to end
- phase/domain structures exist in the tracker schema and UI
- direct MCP mutation tools for phase/domain management are not included yet

## Validation checklist

Run these commands after changes:

```bash
npm run typecheck
npm run build
```

Optional smoke checks:

```bash
node packages/mcp/dist/cli.js get-project-status
node packages/mcp/dist/cli.js list-tasks
```
