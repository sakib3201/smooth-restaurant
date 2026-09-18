---
title: 17 UI & UX Design System
tags:
  - smooth-restaurant/design
  - rnd
aliases:
  - Design System
  - UI UX
  - Look and Feel
---

# 17 — UI & UX Design System

> [!abstract] What this is
> The canonical look-and-feel contract for **Smooth Restaurant**. Locks brand feel, color tokens, type, shape/density, motion, components, and the accessibility floor.
> Screens that consume this system live in [[14-ideal-product#3. Screen-by-screen ideal experience]]; implementation (token files, lint rules) lives in [[16-technical-details]]; scope timing in [[07-roadmap-milestones]].

> [!success] Locked 2026-09-06 via founder interview
> 7 design decisions + token-contract call, all approved section by section. Accent direction **B (Herb & Charcoal)** selected on a visual 3-way comparison (browser clicks recorded).
> - **Feel:** calm neutral ops-tool — quiet UI, food photography carries the color
> - **Quality-bar surface:** diner ordering flow (menu → item sheet → cart → checkout) — every token/component proves itself there first
> - **Theme relationship:** hybrid — Smooth owns the food-commerce layer via `--smooth-*` tokens, inherits theme fonts
> - **Typography:** theme fonts for diner flows + OS system stack for admin/queue/KDS; zero webfonts shipped
> - **Accent:** deep herb on calm paper neutrals (Herb & Charcoal)
> - **Shape:** split density — soft + roomy for diners, compact for ops
> - **Motion:** subtle only (150–300ms, reduced-motion gated)
> - **Enforcement:** strict token contract — tokens are law, CI-linted, agents get the table not opinions

## 1. Principles (the 7 rules every later choice obeys)

1. **Calm is the brand.** Neutral UI, food carries the color. One accent, spent only on price, primary CTA, and live states — never decoration.
2. **Diner flow sets the bar.** Every token and component must first prove itself on the phone ordering flow; staff/owner surfaces inherit, then densify.
3. **Respect the theme.** Inherit theme fonts; own only the food-commerce layer (menu cards, item sheet, cart, checkout, queue, KDS). Smooth looks like Smooth where money moves, like WordPress everywhere else.
4. **Speed is visual.** No webfonts, system stacks, subtle motion only. Any decorative choice that breaks the perf budget ([[14-ideal-product#5. Performance budgets per screen]]) loses to the budget.
5. **Tokens are law.** `--smooth-*` tokens, CI-linted. Agents don't get color opinions — they get the table (§8).
6. **No nagware, ever.** Pro upsells appear once, contextually, dismiss-forever. Visual generosity is the Free-tier brand (anti-Five-Star pain, see [[12-competitor-deep-dive]]).
7. **Accessible by default.** AA contrast, 44px targets, keyboard paths, RTL — designed in, not audited later.

## 2. Color + tokens (Herb & Charcoal)

| Token | Light — FOH/diner/admin | Dark — KDS/night queue | Used for |
|-------|------------------------|------------------------|----------|
| `--smooth-bg` | `#F5F7F4` paper | `#0E1511` green-charcoal | page background |
| `--smooth-surface` | `#FFFFFF` | `#16211A` | cards, sheets, queue cards |
| `--smooth-ink` | `#101A14` | `#EAF2EC` | primary text |
| `--smooth-muted` | `#5F6B62` | `#9DB0A4` | secondary text |
| `--smooth-line` | `#DFE5DC` | `#24352A` | borders, dividers |
| `--smooth-accent` | `#1F6B45` deep herb | `#57B47E` lightened tonal variant | price, primary CTA, active/live states |
| `--smooth-accent-ink` | `#FFFFFF` (≈6:1 on accent) | `#0B120D` | text on accent |
| `--smooth-accent-soft` | `#E4EFE8` | `#1B2E22` | selected-row wash |
| `--smooth-warning` | `#92400E` | `#E5A552` | warnings + honest-slot copy |
| `--smooth-danger` | `#B91C1C` | `#F08A8A` | destructive, errors, overdue timers |
| `--smooth-info` | `#47617E` | `#9DB8D2` | neutral notices |

Rules:
- **Accent spend:** price, the single primary CTA per screen, active/selected and live-timer states — ≈5% of pixels, never decoration.
- **Dark pairs are designed, not derived.** Never invert light values; each dark pair is contrast-verified independently (AA: 4.5:1 text, 3:1 large glyphs).
- **No color-only meaning.** Status, errors, timers and availability always pair hue with icon, label, or position (KDS timers: color + text state).
- **Tokens are direction- and motion-agnostic.** RTL and reduced-motion change nothing about color.
- Rejected directions (recorded, don't relitigate): A Terracotta Ember (warm, appetizing but heavier), C Saffron & Slate (premium but amber needs constant discipline to stay calm).

## 3. Typography

- **Diner flows inherit the theme's font stack** (zero added payload, agency-loved). **Admin/queue/KDS use the OS system stack** (`-apple-system, "Segoe UI", Inter fallback`).
- Base **16px**, line-height **1.5–1.75**; scale `12 / 14 / 16 / 18 / 24 / 32`; weights 400 body / 500 labels / 600–700 headings.
- **Tabular numerals** for prices, timers, and totals (no layout shift as numbers tick).
- **No webfonts shipped, ever** — this is what keeps the menu ≤50KB budget honest. Distinctiveness comes from spacing, rhythm, and food photography, not a typeface.
- Line length: 35–60 chars mobile, 60–75 desktop.

## 4. Shape + density (split)

- **Diner density (default):** large radii (16–20px cards, full-round pills for Add/CTA/pay), roomy 16–24px spacing, 48px+ touch targets. Friendly, tappable, calm.
- **Ops density (queue / KDS / dashboard):** compact token — 8–12px radii, 8–12px rows, same accent and type, more orders per screen.
- One **4/8pt spacing scale** across both densities; density never changes color or type.
- Elevation: one restrained shadow scale for sheets/cards/modals; no random shadow values.

## 5. Motion (subtle only)

- Micro-interactions **150–300ms ease-out** on enter, **~60–70% of enter duration** on exit; transform/opacity only (never width/height/top/left).
- Press feedback on every tappable element (opacity/scale, within 100ms).
- **Skeleton screens** for loads over 1s; no long blocking spinners; no entrance choreography, no decorative animation.
- Everything gated behind **`prefers-reduced-motion`**; layout never shifts as a result of animation.

## 6. Components + patterns

- **One SVG icon set** (single stroke width, 24px grid, Lucide-style), **one toast system**, **one empty-state illustration style**, one focus-ring style.
- Canonical patterns — each proven on the diner flow first: **bottom-sheet** (item), **slide-over** (cart), **sticky category nav** with veg/spicy filters, **one-page checkout** with idempotent Pay button, **queue cards with timers** (staff), **agenda** (reservations), **insight callouts** in plain English (owner).
- **Print stylesheets** for receipts and QR table cards are part of the system, not an afterthought.
- Each screen has exactly **one primary CTA**; secondary actions stay visually subordinate.
- Form rules: visible labels (never placeholder-only), errors below the field with a recovery path, validate on blur, first-invalid-field focus on submit.

## 7. Accessibility floor

- **AA contrast** on every token pair, both themes, verified separately (body ≥4.5:1, large ≥3:1).
- **44px minimum targets** with 8px gaps; visible **2–4px focus rings**; tap, never hover-dependent.
- Full **keyboard + screen-reader paths** for checkout and KDS (tab order = visual order, live regions for order status, no color-only cues).
- **RTL from day 1**, logical properties throughout; D7 lock holds.
- Touch: 48px+ diner targets, safe-area aware sheets, no content behind fixed bars.

## 8. Enforcement — tokens are law

1. Tokens ship as `--smooth-*` custom properties with a **`theme.json` mapping**; components reference tokens, never raw hex.
2. **CI lints for raw hex** in component CSS (allow-listed only in the tokens file itself); a lint failure blocks the money-PR gate in [[07-roadmap-milestones]].
3. **AI agents receive the token table (§2) as mandatory context** — no color opinions, no invented greens (see [[16-technical-details#13.8 AI-assisted development model]]).
4. M1 exit includes **visual QA**: screenshots of menu / item sheet / cart / checkout / queue reviewed against tokens before the money-path gate counts as green.

## 9. Traceability

- Screens → [[14-ideal-product#3. Screen-by-screen ideal experience]] (A1–A5, B1–B5, C1–C4, D1–D5)
- Perf budgets → [[14-ideal-product#5. Performance budgets per screen]] (menu ≤50KB, checkout ≤80KB, 0KB off-Smooth pages)
- Architecture → [[06-technical-architecture]] (D3 Gutenberg-first, S2 shared design system; detail in [[16-technical-details]])
- Scope timing → [[04-feature-map]] Ships column + [[07-roadmap-milestones]] M1/M3/M5
- Personas → [[03-personas-jtbd]] (owner / staff / diner / agency); anti-personas unchanged

## 10. Open questions (M1 implementation picks, not direction)

1. Exact SVG family lock at M1 start (default: Lucide-compatible stroke set).
2. Dark-KDS field validation on a real kitchen tablet with an M2 pilot (glare, viewing distance).
3. Theme font-stack edge cases (CJK/Arabic fallbacks inside the system stack) — spot-check in M1, RTL lock already covers direction.
