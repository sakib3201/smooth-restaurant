# AGENTS.md — tests/

- `Unit/` — no-WordPress suite, stubbed via `Support/WpStubs.php`
  (`sr_test_*` flags, `add_action`/`do_action`/`apply_filters` capture).
  Mirrors `src/` paths. Primary gate.
- `Unit/Architecture/` — static scans enforcing the architecture:
  no-postmeta, register purity, domain purity, boot discipline. New
  discipline → new scan here, not a wiki note.
- `Unit/Database/Support/FakeWpdb.php` — repository test double.
- `Integration/` + `test:wp-env` — need Docker; cannot run locally.
- `e2e/` — Playwright, `workers: 1`, shared WP state, needs Docker.
- `js/` — Jest (`asset-budget.test.ts` = 0KB leak rule).

Do not invent new stub mechanisms — extend `WpStubs.php` patterns.
Rules: `../agent_rules/testing-quality-gates.md`.
