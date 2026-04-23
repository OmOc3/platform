# Command Center

Standalone workspace for the project command center blueprint.

This workspace is intentionally isolated from the Laravel app in the repo root.
It contains:

- `packages/tracker-core`: shared schema, validation, selectors, and safe tracker file I/O
- `packages/mcp`: MCP server and CLI for tracker operations
- `apps/desktop`: Electron dashboard that watches and edits the tracker

The implementation follows the referenced blueprint, with a few corrections:

- runtime schema validation for tracker state and write payloads
- optimistic file versioning and atomic writes to reduce lost updates
- explicit operator guardrails in write tools
- a desktop-side project root picker/config flow instead of env-only setup

The tracker file remains `project-tracker.json` at the target project root.

## Workspace layout

- `packages/tracker-core`: tracker schema, derived selectors, and safe file I/O
- `packages/mcp`: MCP server plus CLI facade for the 24 tracker tools
- `apps/desktop`: Electron operator console with swim lane, task board, agent hub, and calendar
- `docs`: design notes and workflow documentation
- `templates`: starter tracker JSON and prompt templates for operator/agent roles

## Quick start

```bash
npm install
npm run typecheck
npm run build
```

### Desktop app

```bash
npm run dev:desktop
```

The desktop app prompts for a project root on first run and creates `project-tracker.json` if it does not exist.

### MCP server

Create a `.env` file or export `PROJECT_ROOT` first:

```bash
PROJECT_ROOT=../some-project
```

Then run:

```bash
npm run dev:mcp
node packages/mcp/dist/cli.js get-project-status
```

### Windows installer / portable build

```bash
npm run dist:desktop
```

Artifacts are written to `apps/desktop/release/`:

- `Command-Center-Setup-<version>-x64.exe`
- `Command-Center-Portable-<version>-x64.exe`

## Current scope

- 24 MCP tools are implemented: 8 read tools and 16 write tools
- operator-only actions are enforced in the write handlers
- tracker writes are atomic and version-checked
- milestone/task/agent flows are fully implemented
- phase and domain metadata are supported in the schema and UI, with dedicated mutation tools intentionally left as a follow-up surface
- desktop packaging is configured for `nsis` installer plus `portable` Windows output

See `docs/workflow.md` for the recommended operating loop.
