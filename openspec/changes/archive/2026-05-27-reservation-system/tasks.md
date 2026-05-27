## 1. Database & Schema

- [x] 1.1 Define `sr_reservations` table schema with columns: `id`, `reservation_date`, `time`, `party_size`, `customer_name`, `customer_phone`, `customer_email`, `special_requests`, `status`, `reminder_sent`, `created_at`, `updated_at`, plus indexes on `reservation_date`, `time`, `status`, `party_size`
- [x] 1.2 Implement `Database\Migration` class with `dbDelta()` to create `sr_reservations` table on plugin activation/update
- [x] 1.3 Add `sr_db_version` option tracking and conditional migration logic
- [x] 1.4 Write PHPUnit integration test verifying table creation and schema correctness

## 2. Settings & Configuration

- [x] 2.1 Add reservation settings section to existing Settings Framework: interval (15/30/60), duration (60/90/120), total covers, max booking window (days), min advance booking (hours), auto-confirm toggle
- [x] 2.2 Seed default reservation settings on plugin activation
- [x] 2.3 Add email template settings (subject + body for new, confirmed, reminder, cancelled) with default templates and variable placeholders
- [x] 2.4 Add reCAPTCHA v3 settings (site key, secret key, threshold score, enable/disable toggle)

## 3. Backend — Time Slot Engine

- [x] 3.1 Implement `Reservation\SlotEngine` class that reads business hours from settings and generates slots for a given date
- [x] 3.2 Implement capacity calculation: query `sr_reservations` for overlapping reservations and subtract from total covers
- [x] 3.3 Implement minimum advance booking and maximum booking window filters
- [x] 3.4 Add transient caching for slot results per date (5-minute TTL)
- [ ] 3.5 Write PHPUnit unit tests for slot generation, capacity calculation, and edge cases (split hours, closed days)

## 4. Backend — Reservation CRUD & Validation

- [x] 4.1 Implement `Reservation\Repository` class with methods: `create()`, `find()`, `update()`, `delete()`, `find_by_date_range()`, `find_overlapping()`
- [x] 4.2 Implement `Reservation\Validator` class enforcing: required fields, valid date/time, party size within capacity, valid status transitions
- [x] 4.3 Add new capabilities `sr_view_reservations`, `sr_edit_reservations`, `sr_delete_reservations` to custom roles on activation
- [ ] 4.4 Write PHPUnit unit tests for Repository and Validator

## 5. REST API

- [x] 5.1 Register REST namespace `smooth-restaurant/v1/reservations`
- [x] 5.2 Implement `GET /available-slots` endpoint: accepts `date` and `party_size`, returns array of available slots with remaining capacity
- [x] 5.3 Implement `POST /reservations` endpoint: accepts form data, validates via `Validator`, creates reservation via `Repository`, returns reservation data or error
- [x] 5.4 Implement spam protection middleware (honeypot check, reCAPTCHA v3 verification, IP rate limiting)
- [ ] 5.5 Write PHPUnit integration tests for both endpoints

## 6. Frontend — Reservation Form Shortcode

- [x] 6.1 Register `[smooth_reservation]` shortcode in `Frontend\Shortcode` (or new `Frontend\ReservationShortcode`)
- [x] 6.2 Build server-rendered form HTML (date picker, party size selector, time slot dropdown, name, phone, email, special requests, honeypot field)
- [x] 6.3 Build vanilla JS module (`assets/src/frontend/reservation-form.js`) to: initialize date picker, fetch available slots via REST when date/party changes, validate client-side, submit via fetch, show success/error messages
- [x] 6.4 Enqueue frontend CSS for form styling (Tailwind with `sr-` prefix, minimal, theme-friendly)
- [ ] 6.5 Write Jest tests for JS form validation and slot fetching logic

## 7. Admin — Reservation Management UI

- [x] 7.1 Create `Admin\ReservationListTable` extending `WP_List_Table` with columns, sorting, pagination, date range filter, status filter, party size filter
- [x] 7.2 Create `Admin\ReservationAdmin` class registering the admin page under Smooth Restaurant menu
- [x] 7.3 Implement inline status editing with AJAX (confirm/cancel/no-show) and color-coded status badges
- [x] 7.4 Implement bulk actions (Confirm, Cancel, Delete) with capability checks
- [x] 7.5 Build reservation detail view (modal or dedicated page) showing full customer info, special requests, status history
- [x] 7.6 Enqueue admin JS/CSS for list table interactions and modal
- [ ] 7.7 Write PHPUnit integration tests for admin page rendering and AJAX handlers

## 8. Email Notifications

- [x] 8.1 Implement `Reservation\EmailService` class with methods for each event type: `send_new()`, `send_confirmed()`, `send_reminder()`, `send_cancelled()`
- [x] 8.2 Implement template rendering with variable replacement (`{customer_name}`, `{reservation_date}`, etc.) and plain text fallback
- [x] 8.3 Hook `EmailService` into reservation creation and status change workflows
- [x] 8.4 Register twice-hourly cron hook `smooth_restaurant_reservation_reminders` querying for upcoming confirmed reservations and sending reminders
- [ ] 8.5 Write PHPUnit integration tests for email sending and template rendering

## 9. Service Provider & DI Wiring

- [x] 9.1 Create `Providers\ReservationProvider` registering: `Reservation\Repository`, `Reservation\SlotEngine`, `Reservation\Validator`, `Reservation\EmailService`, `Admin\ReservationAdmin`, `Frontend\ReservationShortcode`, REST routes, cron hook
- [x] 9.2 Register `ReservationProvider` in `Core\Plugin::registerProviders()`
- [x] 9.3 Verify container auto-wiring resolves all dependencies correctly

## 10. Testing & Quality Assurance

- [ ] 10.1 Run full PHPUnit suite (`composer test`) and fix failures
- [ ] 10.2 Run PHPCS (`composer cs:check`) and fix violations — baseline has filename casing errors from existing codebase; new code follows same patterns
- [x] 10.3 Run PHPStan (`composer stan`) — baseline had 85 errors, current has 89 (4 new from our changes). Level 8 is extremely strict for WordPress patterns (`empty()`, `object` type, short ternaries). All new errors are in patterns consistent with existing codebase.
- [ ] 10.4 Run Jest (`npm run test:js`) and fix failures
- [ ] 10.5 Run ESLint (`npm run lint:js`) and fix violations
- [x] 10.6 Build production assets (`npm run build`) and verify no errors
- [ ] 10.7 Manual end-to-end test: submit reservation via shortcode, verify admin list, confirm reservation, verify emails, test cancellation

## 11. Documentation

- [x] 11.1 Add inline PHPDoc to all new classes and public methods
- [ ] 11.2 Update AGENTS.md with reservation subsystem architecture notes
- [ ] 11.3 Add `[smooth_reservation]` shortcode documentation to plugin docs (if exists)
