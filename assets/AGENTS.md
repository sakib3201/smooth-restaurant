# AGENTS.md — assets/

Gutenberg-first frontend. Three webpack bundles (`admin`, `frontend`,
`blocks`) via `@wordpress/scripts`; entries under `src/`.

- `src/shared/content-gate.ts` — `hasSmoothContent()` mirrors PHP
  `smooth_should_load()` (Gutenberg `wp-block-smooth-*` classes +
  `data-smooth` hatch). Frontend MUST NOT boot without it (0KB rule).
- Aliases `@/admin|frontend|shared|blocks` (jest + webpack mapped).
  Tailwind prefix `sr-`. `admin/components/ui/` = shadcn/base-nova.
- Entries are thin; keep leak-relevant logic in `shared/` where Jest
  covers it (`tests/js/`).
- `npm ci` only (lockfile committed). `npm run build` before asserting
  anything about `.asset.php` manifests.

Rules: `../agent_rules/coding-conventions.md`,
`../agent_rules/testing-quality-gates.md`.
