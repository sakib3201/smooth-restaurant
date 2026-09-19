# Smooth Restaurant — WordPress Restaurant Management Plugin (Menus, Ordering, Reservations & QR Ordering)

[![License: GPL-2.0-or-later](https://img.shields.io/badge/license-GPL--2.0--or--later-blue)](readme.txt)
[![PHP >= 8.2](https://img.shields.io/badge/PHP-%3E%3D8.2-777BB4)](https://www.php.net/)
[![WordPress >= 6.8](https://img.shields.io/badge/WordPress-%3E%3D6.8-21759B)](https://wordpress.org/)
[![Node 20+](https://img.shields.io/badge/Node-20%2B-339933)](https://nodejs.org/)

> The restaurant management plugin that actually works for restaurant people — not just website developers.

**Smooth Restaurant** is a complete, standalone restaurant management plugin for WordPress: online food ordering **without WooCommerce**, table reservations, QR-code ordering, kitchen display, and menu management. It is designed staff-first for real hospitality workflows. Cash on delivery and pay-at-counter work out of the box; online payments (Stripe, PayPal, Square) arrive via the Pro tier.

Whether you need a *WordPress restaurant plugin* for *online food ordering without WooCommerce*, a *table reservation system*, or *QR code ordering* — Smooth Restaurant covers the full front-of-house stack.

## Why Smooth Restaurant

- **Standalone by design** — no WooCommerce required. No product/variation modelling forced onto food menus, no general-purpose checkout stack to maintain.
- **Staff-first UX** — big buttons, clear text, touch-optimized screens. Non-technical staff can run service without breaking the site.
- **Generous free tier** — enough to run a real restaurant: menus, COD ordering, reservations, QR ordering, thermal printing.
- **Developer-grade engineering** — custom tables (no postmeta abuse), capability-gated REST API (`smooth/v1`), Gutenberg bindings, PHPStan level 8, a green unit suite, and Bruno API contracts.

## Features

### Menu Management
- Menu sections, items, and modifier/add-on groups with drag-and-drop ordering
- Live prices in integer cents (no float rounding at checkout), dietary badges, allergen warnings
- Gutenberg `smooth/menu` bindings render live custom-table rows; autosave-safe by design

### Online Ordering (no WooCommerce)
- Full cart and checkout: cash on delivery / pay at counter by default
- Append-only transaction ledger, server-calculated totals (line → discount → tax → fee pipeline)
- Customer-facing order status tracking page

### Table Reservations
- Time-slot generation, instant or manual confirmation, automated reminders
- Capacity-aware booking rules

### QR Code Ordering & Kitchen Flow
- Per-table QR codes for contactless dine-in ordering
- Full-screen Kitchen Display System (KDS) with auto-refresh and sound notifications
- Thermal (ESC/POS) printer support: WebUSB for USB printers, WebSocket proxy for network printers

### Roles & Access
- Custom roles out of the box: Staff, Kitchen, Manager, Driver
- Granular `smooth_manage_menus` capability mapped through `map_meta_cap`

## Requirements

| Dependency | Version |
| ---------- | ------- |
| PHP | 8.2+ |
| WordPress | 6.8+ |
| Node.js / npm (development only) | 20+ / 10+ |
| Docker (E2E tests only) | `@wordpress/env`, Playwright |

## Install (site owners)

1. Upload the plugin files to `/wp-content/plugins/smooth-restaurant`, or install through the WordPress plugins screen.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Configure your restaurant profile under **Settings** and add your menu items.
4. Place the `[smooth_menu]` shortcode (menu display) and `[smooth_reservation]` shortcode (table bookings) on any page.

See [readme.txt](readme.txt) for the WordPress.org listing, FAQ, and screenshots.

## Developer guide

### Setup

```bash
git clone https://github.com/sakib3201/smooth-restaurant.git
cd smooth-restaurant
composer install
npm ci
```

### Workflow

```bash
npm run dev    # development build with hot reload
npm run build  # production bundles (admin, frontend, blocks)
```

### Testing

```bash
composer quality      # full PHP gate: code style, PHPStan L8, unit suite
composer test:unit    # PHPUnit unit suite (no WordPress; stubbed)
npm run test:js       # Jest (jsdom, ts-jest)
npm run test:e2e      # Playwright (needs Docker + wp-env, workers: 1)
```

REST contracts live in [`api-docs/`](api-docs/openapi.json), with Bruno requests under `api-docs/smooth-v1/`. The `local` environment plus an app password exercises the `auth-local` write paths; CI runs credential-free.

### Linting & formatting

```bash
composer cs:check   # PHPCS (WordPress Coding Standards)
composer cs:fix     # auto-fix
composer stan       # PHPStan level 8
npm run lint:js     # ESLint
npm run lint:css    # Stylelint
npm run format      # Prettier check
```

### Translations & release

```bash
npm run i18n        # regenerate POT + JSON language files
```

Ensure `composer quality` and `npm run test:js` pass, build production assets, then package the plugin excluding `node_modules`, `vendor`, `tests`, and `.git`. Pull requests target `development` (or `release/*`) — CI fires only on those bases.

## Project status

Smooth Restaurant is under active development. The end goal is a complete, standalone restaurant management plugin for WordPress — from menu authoring to ordering, payments, and dine-in — that restaurant staff can run without developer help.

## Contributing

Contributions are welcome:

1. Fork the repository and create a feature branch (`sakib3201/smo-123-short-slug` style for tracked issues).
2. Follow the existing code style (WordPress Coding Standards for PHP, `@wordpress/scripts` conventions for JS) and the architecture rules in `agent_rules/`.
3. Write tests for new features and bug fixes (money-path changes ship matching tests).
4. Ensure `composer quality` and the JS gates pass, update docs, and open a PR against `development`.

## License

Smooth Restaurant is free software licensed under the **GNU General Public License v2.0 or later** (GPL-2.0-or-later). See [readme.txt](readme.txt) for full license details.
