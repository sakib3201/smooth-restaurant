# Commits & PRs

> Guidelines, not law. Match the existing style; optimize for a readable
> `git log` and reviewable diffs.

## Commits

- Style: `type(scope): subject` — e.g. `feat(checkout): ...`,
  `fix(review): ...`, `docs(opsx): ...`, `ci(SMO-123): ...`.
- Common types in history: `feat`, `fix`, `docs`, `ci`, `chore`, `style`, `test`.
- Referencing the Linear issue (`SMO-123`) in the subject is customary
  for issue-driven work but not mandatory for chores.
- One logical change per commit; commit review-fix layers separately
  from stream merges so reviewers can follow the story.
- Never commit secrets. Never amend a commit that hooks rejected —
  fix and commit anew.

## Branches

- Two styles in use, both fine: `sakib3201/smo-123-short-slug`
  (issue work, matches Linear's suggested branch) and short topical
  names (`fix-money-data`, `cpa-backend`) for orchestrated streams.
- One worktree per issue branch; max 2 in flight (see
  `worktrees-subagents.md`).

## PRs

- Target `development` or `release/*` — CI fires only on those bases.
  Feature branches run nothing until they open an integration PR.
- Fill `.github/pull_request_template.md` (gates checklist + money-path
  section). Add the `money-path` label when touching totals, ledger,
  webhooks, migrations, or repositories.
- Keep PRs mergeable: rebase or merge trunk before review when asked;
  never force-push shared branches.
