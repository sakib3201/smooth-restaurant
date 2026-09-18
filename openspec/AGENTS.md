# AGENTS.md — openspec/

Specs of record + ephemeral change workspace.

- `specs/` — source of truth (`provider-architecture`, `data-access`,
  `coding-standards`, `ci-gates`). Read before designing; never edit
  directly for in-flight work.
- `changes/<name>/` — proposal/design/specs/tasks per change.
- `changes/archive/YYYY-MM-DD-<name>/` — completed changes.
- Use the `openspec-*` skills (`propose`/`apply`/`archive`), not
  hand-rolled artifacts.

Rules: `../agent_rules/linear-openspec-workflow.md`.
