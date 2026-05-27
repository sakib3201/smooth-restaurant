## ADDED Requirements

### Requirement: Time slot algorithm respects business hours and booking constraints
The system SHALL generate available reservation time slots based on the restaurant's configured business hours for the selected day of week, the configurable reservation interval (15, 30, or 60 minutes), and the default reservation duration. The system SHALL exclude slots that fall outside business hours or violate the minimum advance booking window.

#### Scenario: Slots align with lunch and dinner split hours
- **WHEN** the restaurant has split hours on Monday (Lunch 11:00-15:00, Dinner 17:00-22:00) with a 30-minute interval
- **THEN** the system generates slots from 11:00-14:30 and 17:00-21:30, with no slots between 15:00-17:00

#### Scenario: Minimum advance booking prevents last-minute reservations
- **WHEN** the minimum advance booking is set to 4 hours and the current time is 10:00
- **THEN** the earliest available slot on the current day is 14:00 or later

#### Scenario: Maximum booking window limits how far ahead reservations can be made
- **WHEN** the maximum booking window is set to 30 days
- **THEN** the date picker only allows selection up to 30 days from today

---

### Requirement: Capacity calculation accounts for overlapping reservations
The system SHALL calculate remaining capacity for each time slot by summing the party sizes of all existing reservations whose time range overlaps with the slot's time range (based on default reservation duration). The system SHALL not allow a reservation to be created if the party size exceeds the remaining capacity.

#### Scenario: Overlapping reservations reduce capacity
- **WHEN** a reservation exists at 19:00 for 90 minutes (until 20:30) with party size 8
- **THEN** the 19:30 slot (also 90 minutes, until 21:00) shows reduced capacity because the 19:00 reservation overlaps

#### Scenario: Reservation blocked when capacity is full
- **WHEN** total covers are 20 and existing overlapping reservations sum to 20
- **THEN** the system returns no available slots for that time window and displays a "Fully booked" message

#### Scenario: Party size limited by remaining capacity
- **WHEN** a time slot has 6 remaining covers
- **THEN** the party size selector only allows values from 1 to 6 for that slot
