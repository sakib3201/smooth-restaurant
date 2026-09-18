# AGENTS.md — Smooth Restaurant (router)

WordPress restaurant-management plugin. PHP 8.2+, WP 6.8+, Node 20+.
Rebuild trunk: `rebuild`. Integration branch: `development` (CI fires only
on PRs targeting `development` / `release/*`).

## Route by task, then read the linked file before acting

| Task | Read first |
|---|---|
| Write PHP/TS, touch providers/domains/DB | `agent_rules/coding-conventions.md` + the nearest per-dir `AGENTS.md` |
| Money, ledger, migrations, repositories | `agent_rules/security-data.md` + `src/Database/AGENTS.md` |
| Run tests, claim green, open a PR | `agent_rules/testing-quality-gates.md` |
| Commits, branches, PR targets | `agent_rules/commits-prs.md` |
| Linear issue, spec, change lifecycle | `agent_rules/linear-openspec-workflow.md` |
| Worktrees, parallel streams, subagents | `agent_rules/worktrees-subagents.md` |
| Something broke oddly (Windows/CI/env) | `agent_rules/gotchas-caveats.md` first |
| Where does this belong? | `agent_rules/folder-structure.md` |
| CI workflows, required checks, PR template | `.github/AGENTS.md` |
| Product/design context | `sm_docs/` (read-only source of truth) |

## Per-directory context (progressive loading)

`src/Core/`, `src/Providers/`, `src/Domains/`, `src/Database/`,
`src/Contracts/`, `src/Testing/`, `tests/`, `assets/`, `.github/`,
`openspec/`, `agent_rules/` each carry their own `AGENTS.md` — read the
one next to the code you touch. Skills live in `.opencode/`,
`.agents/`, `.claude/` (referenced, not duplicated here).

## Non-negotiables (details in linked files)

- Evidence before assertions; `composer quality` + relevant JS gates green.
- Linear leads: spec on issue, branch/worktree per issue, max 2 in flight.
- Money path: append-only ledger, matching tests in the same PR.
- No secrets in repo or workflows. LF line endings. PHP 8.2-compatible code.
