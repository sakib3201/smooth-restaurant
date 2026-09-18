# Gotchas & Caveats

> Session scars. Read before debugging the harness instead of the code.

## Line endings (Windows)

- Repo is LF. PowerShell `Set-Content` / `Get-Content | Set-Content`
  pipelines write CRLF — always normalize after bulk rewrites:
  read bytes, `-replace "`r`n","`n"`, `WriteAllText`. Verify with
  `git diff --stat` (whole-file rewrites = damage).
- Same hazard: `npm install` rewrote six tracked files' endings once.
  After any `npm`/`composer` run, check `git status` for unintended
  modifications and revert them.

## PowerShell quoting

- `|` inside quotes still splits pipelines when invoking `.bat`
  shims (`vendor\bin\phpunit --filter 'a|b'`). Run PHP tools via the
  interpreter instead: `php vendor/phpunit/phpunit/phpunit ...`.
- `Get-Content` on PS 5.1 has no `-Raw`; `foreach`, not `for (.. in ..)`.
- Native stderr noise (`CategoryInfo ... NativeCommandError`) is often
  cosmetic — check the actual output, not the red text.

## Tooling environment

- Local PHP (8.5) is NEWER than the floor (8.2): write 8.2-compatible
  code; never use a newer syntax even if it runs locally.
- No Docker here: `wp-env`, E2E, and integration suites cannot run
  locally. Unit substitutes + CI runs are the evidence path.
- `composer install` may fail on GitHub dist auth; mirror `vendor/`
  from a same-commit checkout as fallback.
- GitHub Actions needs a healthy billing state; a billing lock fails
  every job before it starts ("job was not started").
- `npx --yes js-yaml <file>` validates workflow YAML when Python is absent.

## Repo-specific traps

- `UNIQUE` + `DEFAULT ''` on a column = second keyless insert fatals.
  Key columns carry no default; writers generate keys.
- Zero-date datetime defaults break strict-mode MySQL; use
  `DEFAULT CURRENT_TIMESTAMP`.
- `Money` is intentionally bound as a zero-USD singleton anchor in
  `CheckoutProvider` (tested design) — do not "clean it up".
- `BlocksProvider` contexts include admin while its guard bails there;
  the blocks follow-up owns the real editor-context fix.
- `smooth_should_load()` builds a throwaway container only as fallback;
  prefer the bound singleton.
- `update_site_option()` takes no autoload parameter (only the
  `update_option()` fallback path sets autoload false).
