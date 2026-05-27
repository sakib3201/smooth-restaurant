## Why

Smooth Restaurant needs a reservation system to match the core functionality offered by competitors like Five Star and ReDi. Even ordering-focused restaurants need table reservations — it is a baseline expectation for restaurant management plugins. Without it, the plugin cannot serve the full operational needs of a single restaurant (Phase 1 goal). This change delivers the foundational reservation feature set: frontend booking via shortcode, time-slot availability engine, and admin reservation management.

## What Changes

- **New database table** `sr_reservations` for storing reservation records with proper indexing on `reservation_date`, `time`, `status`, and `party_size`.
- **New shortcode** `[smooth_reservation]` rendering a reservation form with date picker, time slot dropdown, party size selector, and contact fields. Includes honeypot + reCAPTCHA v3 spam protection.
- **Time Slot Generation Engine** that calculates available slots based on restaurant business hours, configurable interval (15/30/60 min), default duration, and remaining capacity (total covers minus existing reservations).
- **Admin Reservation Management** — a dedicated WP_List_Table-based screen with filtering by date/status/party size, sortable columns, bulk actions (confirm/cancel/delete), inline editing, and color-coded status indicators.
- **Reservation Confirmation Emails** — template-based emails for new request, confirmation, reminder (cron-driven, 2h before), and cancellation. Sent to both customer and restaurant.
- **REST API endpoints** for frontend availability checking and reservation submission (used by shortcode and future blocks).
- **New service provider** `ReservationProvider` wiring admin menu, assets, shortcode, and REST routes into the DI container.
- **New frontend assets** for the reservation form (React/vanilla JS date picker integration, time slot fetching, form validation).
- **New admin assets** for the reservation list view (status filters, inline editing, AJAX status updates).

## Capabilities

### New Capabilities
- `reservation-form`: Frontend reservation form shortcode `[smooth_reservation]` with real-time availability checking, spam protection, and instant or manual confirmation flow.
- `reservation-time-slots`: Server-side time slot generation engine based on business hours, interval settings, reservation duration, and remaining table capacity.
- `reservation-admin`: Admin reservation management interface (list view, filtering, bulk actions, inline editing, status workflow).
- `reservation-emails`: Template-based email notifications for reservation lifecycle events (new, confirmed, reminder, cancelled).

### Modified Capabilities
<!-- No existing capabilities are being modified at the spec level — this is a net-new feature set. -->

## Impact

- **Database**: New custom table `sr_reservations` added to the schema foundation. Migration runs on plugin update.
- **Admin UI**: New top-level menu item "Reservations" under the Smooth Restaurant admin menu with list and detail views.
- **Frontend**: New shortcode `[smooth_reservation]` available on any post/page. Enqueues reservation-specific JS/CSS.
- **REST API**: New namespace `smooth-restaurant/v1/reservations` with endpoints for availability and submission.
- **Email**: Hooks into existing email notification framework (or creates one if not yet present).
- **Dependencies**: Requires restaurant profile settings (business hours from Settings Framework 1.1.4) to generate time slots.
- **Roles/Caps**: New capabilities `sr_view_reservations`, `sr_edit_reservations`, `sr_delete_reservations` mapped to `sr_manager` and `sr_staff` roles.
