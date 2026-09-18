# AGENTS.md — agent_rules/

Guardrail files for AI agents (tool-neutral: OpenCode, Claude, subagents).
Root `AGENTS.md` routes here by task type.

- `coding-conventions.md` — PHP/TS style, architecture discipline, docs-as-code
- `commits-prs.md` — commit/branch/PR naming guidelines, PR targets
- `linear-openspec-workflow.md` — Linear source of truth, OpenSpec lifecycle, money-path rules
- `testing-quality-gates.md` — gates, suites, CI evidence, completion claims
- `worktrees-subagents.md` — worktree hygiene, orchestrator/reviewer pattern
- `gotchas-caveats.md` — session scars (line endings, quoting, env limits)
- `folder-structure.md` — repo map + placement rules
- `security-data.md` — secrets, ledger integrity, WP surface rules

Keep files task-scoped and under ~60 lines each. Cross-link instead of
duplicating. Skills live in `.opencode/`, `.agents/`, `.claude/` —
referenced, never moved here.
