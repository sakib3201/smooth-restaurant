# AGENTS.md — .github/

CI is pull-request gated (see `BRANCH_PROTECTION.md`).

- Triggers: PRs targeting `development` / `release/*`, pushes there,
  `workflow_dispatch`. Feature branches are silent by design.
- Workflows: `quality` (full gate), `php-tests` (+ migration lint
  advisory, `money-path` label suites, evidence uploads), `php-lint`
  (8.2/8.3), `js-lint`, `js-tests` (+ `asset-budget` leak job),
  `e2e-tests` (soft-fail, non-required), `build` (+ Plugin Check on ZIP).
- Runners pinned `ubuntu-24.04`. No `secrets.*` anywhere (grep-verified).
- `pull_request_template.md` is the PR contract (gates checklist +
  money-path section) — point authors at it, don't duplicate it.

Rules: `../agent_rules/testing-quality-gates.md`,
`../agent_rules/commits-prs.md`.
