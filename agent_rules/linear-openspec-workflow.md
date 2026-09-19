# Linear + OpenSpec Workflow

> Linear leads, the board mirrors. OpenSpec changes and local boards are
> ephemeral execution state.

## Linear (source of truth, `linear_*` tools)

- No build starts without a short spec attached to the Linear issue.
  Pull the issue + spec before writing code; link subtasks to the parent.
- Branch/worktree per issue; max 2 in flight.
- Sync back as you go: post progress comments with test evidence, not
  just at the end. Evidence format: gate output
  (`composer quality` → cs/stan/tests counts), never "looks good".
- Blocked tasks get annotated in place (`BLOCKED <date>: cause +
  unblock condition`), never silently left unchecked.
- On completion: archive the OpenSpec change, then set issue status +
  comment with evidence. Record caveats explicitly (e.g. smoke tests
  that need Docker, rollback scope) rather than holding Done hostage
  over environment limits — but say so in the comment.
- Status timing: move to In Progress when work starts, not when the
  branch is created.
- Cycles are journey slices: one end-to-end, demoable feature per cycle;
  guardrails (CI, assets, a11y) ride along as a support track, not a
  cycle of their own. Cycle exit = demo script run on a zero-Woo site +
  evidence (gates, Bruno/e2e), never "merged".

## OpenSpec (`openspec/*`)

- Propose (`openspec-propose`) before multi-step builds; apply
  (`openspec-apply`) to execute; archive (`openspec-archive`) when done.
- Delta specs live in `openspec/changes/<name>/specs/`; archiving syncs
  them to `openspec/specs/`. Never edit `openspec/specs/` directly for
  in-flight work.
- Small fixes skip OpenSpec. Rule of thumb: new capability or >1 session
  of work → change; anything smaller → direct commit.

## Money-path rules

- Ledger is append-only (refunds are new rows, never updates/deletes).
- Totals / ledger / webhook / migration changes require matching tests
  in the same PR — reviewer must reject money-path PRs without them.
