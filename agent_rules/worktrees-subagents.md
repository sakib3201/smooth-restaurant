# Worktrees & Subagents

> One worktree per issue branch; max 2 in flight. Main checkout stays on
> the trunk (`rebuild`).

## Worktrees

- Create: `git worktree add .worktrees/<slug> -b <branch> <base>`
  (usually base `rebuild`). Short slugs — deep nesting plus
  `node_modules`/`vendor` breaks Windows path limits.
- Untracked files (`.env`, planning dirs, `vendor/`, `node_modules/`)
  do NOT follow worktrees. Copy what a stream needs explicitly, and
  copy it back (or commit it on the branch) before removing the worktree.
- Remove: `git worktree remove --force .worktrees/<slug>`
  (+ `git worktree prune`). If removal fails on path length, retry with
  `npx --yes rimraf .worktrees/<slug>` then `prune`.
- `composer install` needs network + GitHub auth; without it, mirror
  `vendor/` from a checkout at the same commit. Same for
  `node_modules` (or `npm ci`, registry permitting).
- Merging streams: `--no-ff` with a scope message; fast-forward trunk
  catch-ups. Delete merged branches promptly; never merge discarded
  eras (e.g. pre-reset sprint branches) back into the trunk.

## Orchestrator / reviewer pattern

- Dispatch at most 2 streams in parallel with disjoint file scopes;
  a third waits for a slot. Give each stream exact paths, acceptance
  criteria, gates to run, and the return format (files changed, test
  evidence, deviations).
- After merge, the orchestrator owns a reviewer pass: re-read diffs,
  run the full gate, fix fallout, then commit. Prefer leaving tested
  subagent design choices alone over churning for taste.
- If subagent dispatch fails (API errors), retry once, then do the
  stream solo in its worktree — same gates, same return format.

## Discipline

- Verify a worktree is clean (`git status`) before removing it.
- Keep `git worktree list` at 1 (main only) when idle.
- Never leave stale branches: merged → delete; discarded era → delete
  without merging.
