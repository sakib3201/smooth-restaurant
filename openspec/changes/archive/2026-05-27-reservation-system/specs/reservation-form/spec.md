## ADDED Requirements

### Requirement: Customer can view and submit a reservation form
The system SHALL render a reservation form via the `[smooth_reservation]` shortcode that collects date, time, party size, name, phone, email, and special requests. The form SHALL only display time slots that have available capacity for the selected date and party size.

#### Scenario: Form renders with available time slots
- **WHEN** a visitor views a page containing the `[smooth_reservation]` shortcode
- **THEN** the system displays a reservation form with a date picker, party size selector, and a time slot dropdown populated with available slots for the selected date

#### Scenario: Customer submits a valid reservation
- **WHEN** a customer selects a date, available time slot, party size (within capacity), and provides name and phone, then submits the form
- **THEN** the system creates a reservation record with status `pending` or `confirmed` (based on auto-confirm setting) and sends a confirmation email

#### Scenario: Customer selects a party size exceeding remaining capacity
- **WHEN** a customer selects a party size greater than the remaining covers for a given time slot
- **THEN** the system disables that time slot or shows an error message indicating insufficient capacity

#### Scenario: Spam bot submits form
- **WHEN** a submission fails the honeypot check or reCAPTCHA v3 score is below the threshold
- **THEN** the system rejects the submission without creating a reservation and returns a generic error

---

### Requirement: Time slot generation is accurate and capacity-aware
The system SHALL generate reservation time slots based on restaurant business hours, configurable interval (15, 30, or 60 minutes), default reservation duration, and total covers. The system SHALL subtract existing reservations (within the duration window) from total capacity to determine remaining covers per slot.

#### Scenario: Slots generated for a standard business day
- **WHEN** the restaurant business hours are Mon-Fri 11:00-22:00 with a 30-minute interval and 90-minute duration
- **THEN** the system generates slots at 11:00, 11:30, 12:00 ... 20:30 for any date within the booking window

#### Scenario: Slot capacity reduced by existing reservations
- **WHEN** the restaurant has 40 total covers and 2 existing reservations at 19:00 with party sizes 4 and 6
- **THEN** the 19:00 slot shows remaining capacity of 30 covers

#### Scenario: Slot excluded due to insufficient advance notice
- **WHEN** the minimum advance booking setting is 2 hours and the current time is 14:00
- **THEN** slots before 16:00 on the current day are not offered

#### Scenario: No slots on closed days
- **WHEN** a customer selects a date on which the restaurant is closed
- **THEN** the system returns no available slots and displays a "Closed" message

---

### Requirement: Admin can manage reservations
The system SHALL provide a dedicated admin screen listing all reservations with columns for date, time, party size, customer name, phone, status, and submitted date. The list SHALL support filtering by date range, status, and party size; sorting by date/time; and bulk actions (confirm, cancel, delete). Inline status editing and color-coded status indicators SHALL be supported.

#### Scenario: Admin views today's reservations
- **WHEN** an admin with `sr_view_reservations` capability opens the Reservations admin page
- **THEN** the system displays a list of reservations default-filtered to today's date, sorted by time ascending

#### Scenario: Admin confirms a pending reservation
- **WHEN** an admin clicks "Confirm" on a pending reservation
- **THEN** the system updates the reservation status to `confirmed` and sends a confirmation email to the customer

#### Scenario: Admin cancels a reservation
- **WHEN** an admin clicks "Cancel" on a reservation
- **THEN** the system updates the status to `cancelled` and sends a cancellation email to both customer and restaurant

#### Scenario: Admin uses bulk actions
- **WHEN** an admin selects multiple reservations and applies the "Confirm" bulk action
- **THEN** the system updates all selected reservations to `confirmed` and sends confirmation emails

---

### Requirement: Reservation emails are sent at lifecycle events
The system SHALL send template-based emails for the following events: new reservation request (customer), reservation confirmed (customer + restaurant), reservation reminder 2 hours before (customer), reservation cancelled (customer + restaurant). Email subjects and bodies SHALL be customizable via settings with variable replacement.

#### Scenario: New reservation email sent
- **WHEN** a reservation is successfully submitted
- **THEN** the customer receives an email with the reservation details and current status

#### Scenario: Confirmation email sent on status change
- **WHEN** an admin confirms a pending reservation
- **THEN** the customer receives a confirmation email and the restaurant receives a notification email

#### Scenario: Reminder email sent before reservation
- **WHEN** a reservation is confirmed and the current time is 2 hours before the reservation time
- **THEN** the system sends a reminder email to the customer via the scheduled cron job

#### Scenario: Cancellation email sent
- **WHEN** an admin cancels a confirmed reservation
- **THEN** both the customer and restaurant receive a cancellation email with the reservation details

---

### Requirement: Reservation data is stored securely and query-efficiently
The system SHALL store reservations in the `sr_reservations` custom table with columns for all reservation fields, timestamps, and status. The table SHALL have indexes on `reservation_date`, `time`, `status`, and `party_size` to support fast filtering and slot generation queries.

#### Scenario: Database table created on activation
- **WHEN** the plugin is activated or updated to a version requiring the reservations table
- **THEN** the system creates the `sr_reservations` table with correct schema and indexes via `dbDelta()`

#### Scenario: Reservation data is sanitized on input
- **WHEN** a reservation is created or updated via frontend or admin
- **THEN** all text fields are sanitized with `sanitize_text_field()`, emails with `sanitize_email()`, and party size is cast to a positive integer

#### Scenario: Reservation deletion requires capability check
- **WHEN** a delete request is made for a reservation
- **THEN** the system verifies the user has `sr_delete_reservations` capability before executing the deletion
