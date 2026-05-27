## Context

Smooth Restaurant is a WordPress plugin for restaurant management built on a lightweight DI container (`SmoothRestaurant\Core\Container`), PSR-4 autoloading under `src/`, and a provider-based architecture. The current codebase has `CoreProvider` registering `AdminMenu`, `Admin\Assets`, and `Frontend\Shortcode`. There is no reservation subsystem yet.

The plugin targets PHP 8.1+, WP 6.4+, and uses `@wordpress/scripts` for JS/TS builds (admin, frontend, blocks). The database schema foundation (1.1.6) calls for custom tables: `sr_orders`, `sr_order_items`, `sr_order_item_addons`, `sr_reservations`, `sr_inventory_items`, `sr_inventory_logs`. This change implements the `sr_reservations` table and all surrounding functionality.

Business hours and restaurant profile settings (from 1.1.4) are prerequisites for time slot generation. The reservation system must integrate with the existing settings framework and role/capability system (`sr_staff`, `sr_manager`, etc.).

## Goals / Non-Goals

**Goals:**
- Provide a complete, standalone reservation subsystem with no WooCommerce dependency.
- Allow customers to book tables via `[smooth_reservation]` shortcode with real-time availability.
- Give restaurant staff a fast, filterable admin interface to manage bookings.
- Prevent double-bookings through server-side capacity checks.
- Send lifecycle emails (new, confirmed, reminder, cancelled) using customizable templates.
- Support both instant auto-confirmation and manual approval modes (configurable in settings).

**Non-Goals:**
- Visual floor plan / drag-and-drop table management (Phase 3 — 3.4.1).
- Waitlist management (Phase 3 — 3.4.2).
- Reservation deposits / payments (Phase 3 — 3.4.3).
- SMS or WhatsApp notifications (Phase 3 — 3.8).
- Table-level tracking (this release tracks total covers only, not individual tables).
- Multi-location reservation routing (Phase 3 — 3.2.2).

## Decisions

### 1. Custom table `sr_reservations` instead of a custom post type
**Rationale:** The feature list (1.1.6) explicitly calls for custom tables for orders and reservations to avoid post-meta bloat and enable fast date/status queries. Reservations are transactional records with heavy date-range filtering — a custom table with indexed `reservation_date`, `time`, `status`, and `party_size` columns is significantly more performant than `WP_Query` on post meta.
**Alternative considered:** `sr_reservation` CPT with meta boxes — rejected due to scaling concerns and alignment with the schema foundation decision.

### 2. Server-rendered shortcode + vanilla JS over a React block for the frontend form
**Rationale:** The existing shortcode infrastructure (`Frontend\Shortcode`) is already wired. A server-rendered shortcode with lightweight vanilla JS (for date picker init, time slot fetching, form validation) is faster to build, has zero React runtime overhead for a simple form, and aligns with the current architecture. A Gutenberg block equivalent can be added later (1.6.5) without changing the underlying REST API.
**Alternative considered:** React-based block as primary interface — rejected because it requires block registration, build pipeline changes, and is overkill for a form. The block can be a thin wrapper later.

### 3. Time slot generation in PHP (server-side) with REST API exposure
**Rationale:** Availability logic must be authoritative on the server to prevent tampering. The frontend fetches available slots via AJAX/REST for a given date. The algorithm queries existing reservations for that date, subtracts booked covers from total capacity, and returns valid slots.
**Alternative considered:** Client-side slot generation — rejected because it would expose capacity logic and allow bypassing.

### 4. WP_List_Table for admin reservation management
**Rationale:** WordPress core provides `WP_List_Table` which gives sorting, filtering, pagination, and bulk actions with minimal custom code. It is the standard pattern for admin list views in WordPress plugins and aligns with the feature list description (1.4.3).
**Alternative considered:** Custom React admin page — rejected to avoid unnecessary build complexity for a CRUD list view. React is reserved for the dashboard (1.1.3) and more complex UIs.

### 5. WordPress Cron for reservation reminders
**Rationale:** WP Cron is the standard WordPress scheduling mechanism and requires no external infrastructure. A twice-hourly cron job checks for reservations in the next 2 hours that haven't received a reminder and sends the email.
**Alternative considered:** External cron / Action Scheduler — rejected to keep the free tier self-contained with zero additional dependencies.

### 6. Email templates via `wp_mail()` with customizable subjects/bodies in settings
**Rationale:** `wp_mail()` is universally available in WordPress. Templates are stored as options and rendered with simple variable replacement (`{customer_name}`, `{reservation_date}`, etc.). This avoids dependencies on WooCommerce email templates or third-party services.
**Alternative considered:** WooCommerce email system bridge — rejected because the plugin is standalone and must not depend on WooCommerce.

## Risks / Trade-offs

- **[Risk] Time slot algorithm performance on high-volume days** → If a restaurant receives 100+ reservations per day, the slot query could slow down. **Mitigation:** Index `reservation_date` + `status` on `sr_reservations`. Cache slot results per date for 5 minutes using transients.
- **[Risk] WP Cron reliability for reminders** → On low-traffic sites, WP Cron may not fire on schedule. **Mitigation:** Document that high-volume restaurants should set up a system cron trigger. Reminder emails are non-critical (nice-to-have), so occasional delays are acceptable.
- **[Risk] Date/time timezone inconsistencies** → Restaurant timezone vs. WordPress timezone vs. server timezone can cause off-by-one-hour slot errors. **Mitigation:** All reservation dates/times are stored in UTC and converted to the restaurant timezone (from settings) for display and slot generation. Use `wp_date()` for all frontend formatting.
- **[Risk] Spam submissions on public form** → Exposing a public reservation endpoint invites spam. **Mitigation:** Honeypot field (invisible to humans) + optional reCAPTCHA v3 integration. Rate-limit submissions per IP to 5 per hour.
- **[Risk] Schema migration on plugin update** → Adding a new custom table requires a reliable `dbDelta` migration on update. **Mitigation:** Use `dbDelta()` in the activation/upgrade hook. Version the schema and only run when `sr_db_version` option is lower than the target version.

## Migration Plan

1. On plugin activation or update, `dbDelta()` creates `sr_reservations` table if it doesn't exist.
2. New capabilities (`sr_view_reservations`, `sr_edit_reservations`, `sr_delete_reservations`) are added to existing custom roles (`sr_manager`, `sr_staff`, `sr_kitchen`) via the role registration routine.
3. Default email templates are seeded as options on first activation.
4. Default reservation settings (interval: 30 min, duration: 90 min, max covers: 40, auto-confirm: off) are seeded as options.
5. No breaking changes — this is a net-new feature.

## Open Questions

- Should the reservation form support guest checkout (no WP account) exclusively, or also allow logged-in users to pre-fill details from their profile? **Decision:** Guest-only for Phase 1 to align with the ordering system's guest checkout philosophy (2.10.3). Logged-in pre-fill can be added in Phase 2.
- Should the admin list view support a daily/weekly calendar toggle in Phase 1, or only list view? **Decision:** List view only for Phase 1. Calendar view is a Phase 2 enhancement (2.9.x).
- What is the default restaurant capacity source — a new setting or derived from table management? **Decision:** A simple "Total Covers" number in reservation settings for Phase 1. Table-level capacity comes with floor plan (Phase 3).
