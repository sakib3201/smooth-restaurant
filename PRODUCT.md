# Product

## Register

product

## Users

**Primary users:** Restaurant owners, managers, and staff who use WordPress to run their restaurant's digital operations. These are small to mid-sized restaurants: family-owned pizzerias, fast-casual burger joints, ethnic food spots, cafes, and ghost kitchens. Most are not web developers. Many delegate site management to a freelance developer or agency, but day-to-day operations (checking orders, updating menus, managing reservations) are handled by restaurant staff directly.

**Context of use:** The admin panel is accessed from the WordPress backend, typically on a desktop or laptop in the back office during prep hours, or on a tablet at the host stand during service. Users are often interrupted, time-pressured, and context-switching between cooking, serving, and administration. The interface must be scannable at a glance, forgiving of mistakes, and fast to navigate.

**Job to be done:** Monitor incoming orders, update menu items and availability, manage table reservations, configure settings (hours, delivery zones, printers), and check daily performance. The primary task on any given screen depends on the time of day: morning = menu prep and reservation review; lunch/dinner rush = order monitoring and status updates; evening = reporting and settings adjustments.

## Product Purpose

Smooth Restaurant is a WordPress plugin that replaces the fragmented, overpriced, and WooCommerce-dependent restaurant management tools with a single, standalone solution. The admin panel is the operational nerve center: it must make restaurant staff feel in control of their business, not overwhelmed by software. Success looks like a restaurant owner checking orders in 10 seconds, updating a menu price in 3 clicks, and never needing to Google how to do either.

## Brand Personality

**Three words:** Capable, calm, unobtrusive.

- **Capable:** The interface looks like it can handle a Friday night rush without breaking a sweat. Every feature is within reach. Nothing feels missing or half-built.
- **Calm:** Restaurant life is chaotic. The admin panel is not. Clear hierarchy, generous whitespace, and predictable patterns reduce cognitive load.
- **Unobtrusive:** The design serves the task, not itself. No decorative flourishes, no "delight" that slows down a busy user. It gets out of the way.

**Voice:** Direct, helpful, confident. No jargon, no marketing speak. Labels say what they do. Error messages explain what happened and how to fix it. Success states are quiet, not celebratory.

## Anti-references

- **WooCommerce admin:** Overwhelming, e-commerce-centric, irrelevant settings exposed to restaurant staff.
- **FoodMaster's admin:** Dense, dated, information overload. Feels like enterprise software from 2015.
- **RestroPress:** Buggy, inconsistent component vocabulary, modal-heavy workflows that trap users.
- **Generic SaaS dashboard templates:** Hero metrics, gradient cards, identical icon grids, "AI-powered" badges. The "AI slop" look.
- **WordPress native admin (as a target):** We live inside it, but we don't look like it. The wp-admin chrome is functional but visually noisy. Our panel should feel like a focused, modern application within the WordPress frame.

## Design Principles

1. **Staff-first, not admin-first.** Every screen is designed for the person using it during service, not the developer who installed it. Touch targets are large, text is readable, and actions are obvious.

2. **Speed of recognition over speed of interaction.** A user should understand the state of their restaurant in a single glance. Density is fine for data tables, but the hierarchy must be unmistakable.

3. **Progressive disclosure.** Show what's needed for the current task. Hide advanced options behind clear toggles. Never present 20 fields when 5 will do.

4. **Consistency is an affordance.** The same button looks the same everywhere. The same action follows the same pattern. Users build muscle memory.

5. **Fail gracefully, recover obviously.** If an order fails to sync, if a printer is offline, if a reservation conflicts: the interface says so clearly, suggests the next step, and never leaves the user guessing.

## Accessibility & Inclusion

- **WCAG 2.1 AA** as the minimum standard. Target AAA where feasible (contrast, target size, focus indicators).
- **Reduced motion:** All animations respect `prefers-reduced-motion`. No parallax, no bouncing, no spinning loaders.
- **Color blindness:** Status indicators (order states, reservation confirmations) use both color and shape/icon. Never rely on color alone.
- **Cognitive load:** Clear labels, predictable layouts, no timed interactions. Error prevention over error correction.
- **Touch accessibility:** Minimum 44x44px tap targets for all interactive elements on tablet views.
