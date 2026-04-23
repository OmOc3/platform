# Blueprint Adjustments

This workspace implements the provided command center blueprint with a few required corrections.

## Why the blueprint is not used verbatim

The original document describes a solid product surface, but it leaves several implementation risks unresolved:

1. A single shared JSON file is written by multiple actors without lock/version protection.
2. "Operator only" transitions are described, but no runtime enforcement exists.
3. Hydration requires milestone fields that the tool surface cannot fully mutate.
4. The desktop app relies on environment variables only, which is brittle for end users.
5. The tracker schema is defined only at the TypeScript level, not validated at runtime.

## Corrections in this implementation

### 1. Shared tracker core package

Schema, selectors, file access, derived fields, and runtime validation live in `packages/tracker-core`.

### 2. Versioned tracker writes

The tracker includes a monotonically increasing `meta.version` field.
Writers may pass an expected version; stale writes are rejected instead of silently overwriting newer state.

### 3. Atomic persistence

Writes go through a temporary file and rename flow to reduce partial-write corruption.

### 4. Operator guardrails

Operator-only tool handlers require an explicit `actor_role` of `operator`.
The desktop UI uses the same lifecycle rules.

### 5. Desktop project selection

The Electron app supports picking or changing the target project root from the UI and persists that setting locally.

### 6. Schema readiness for extended mutation surface

The schema already supports milestone scheduling, dependencies, phases, and domains.
The current 24-tool surface focuses on milestone, task, note, drift, and agent operations without inventing extra tools beyond the blueprint count.
If the product later needs direct phase/domain editing tools, they can be added without reshaping the tracker model.

These adjustments keep the product intent intact while making the system viable in daily use.
