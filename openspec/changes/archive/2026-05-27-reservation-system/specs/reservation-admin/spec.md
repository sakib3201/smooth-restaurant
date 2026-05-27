## ADDED Requirements

### Requirement: Admin reservation list provides full CRUD and filtering
The system SHALL provide a WP_List_Table-based admin interface at `Smooth Restaurant > Reservations` with columns: ID, Date, Time, Party Size, Customer Name, Phone, Email, Status, Submitted At. The list SHALL support filtering by date range (today / this week / custom), status (pending / confirmed / cancelled / no-show), and party size. Columns SHALL be sortable. Bulk actions SHALL include Confirm, Cancel, and Delete. Status changes via inline editing SHALL trigger appropriate emails.

#### Scenario: Admin filters by status
- **WHEN** an admin selects "Pending" from the status filter dropdown and clicks "Filter"
- **THEN** the list refreshes to show only reservations with `pending` status

#### Scenario: Admin sorts by reservation time
- **WHEN** an admin clicks the "Time" column header
- **THEN** the list re-sorts by time ascending or descending

#### Scenario: Inline status change triggers email
- **WHEN** an admin uses inline editing to change a reservation status from `pending` to `confirmed`
- **THEN** the system updates the record and sends a confirmation email to the customer

---

### Requirement: Reservation status workflow is enforced
The system SHALL support the following reservation statuses: `pending`, `confirmed`, `cancelled`, `no-show`. Valid transitions SHALL be: pending → confirmed, pending → cancelled, confirmed → cancelled, confirmed → no-show. The system SHALL log the timestamp and user for every status change.

#### Scenario: Invalid status transition is rejected
- **WHEN** an attempt is made to change a `cancelled` reservation to `confirmed`
- **THEN** the system rejects the transition and returns an error

#### Scenario: Status change is logged
- **WHEN** an admin changes a reservation status
- **THEN** the system records the new status, previous status, timestamp, and user ID in the reservation meta or audit log
