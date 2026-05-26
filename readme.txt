=== Smooth Restaurant ===
Contributors: smoothrestaurant
Donate link: https://smoothrestaurant.com
Tags: restaurant, menu, ordering, reservation, food-delivery
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 0.1.0
License: GPL-2.0
License URI: https://www.gnu.org/licenses/gpl-2.0.html

The restaurant management plugin that actually works for restaurant people — not just website developers.

== Description ==

Smooth Restaurant is a complete restaurant management plugin for WordPress. Unlike competitors that force WooCommerce complexity on restaurant owners, Smooth Restaurant provides a purpose-built, standalone solution designed specifically for the hospitality industry.

**Core Philosophy:**
* Generous free tier — enough to run a real restaurant
* Staff-first design — big buttons, clear text, zero chance of breaking your site
* Zero WooCommerce complexity required — works standalone out of the box

**Key Features:**

* **Menu Management** — Custom post types for menu items and categories, drag-and-drop ordering, dietary badges, allergen warnings
* **Standalone Ordering** — Full cart and checkout without WooCommerce. Cash on delivery/pay at counter by default
* **Reservations** — Table booking with time slot generation, instant or manual confirmation, automated reminders
* **Kitchen Display System (KDS)** — Full-screen order display for kitchen staff with auto-refresh and sound notifications
* **Thermal Printer Support** — Auto-print orders to ESC/POS thermal printers
* **QR Code Ordering** — Per-table QR codes for contactless ordering
* **Staff-First Interface** — Touch-optimized, distraction-free dashboard for non-technical staff
* **Order Tracking** — Customer-facing order status tracking page
* **Role-Based Access** — Custom roles: Staff, Kitchen, Manager, Driver

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/smooth-restaurant`, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the Settings screen to configure your restaurant profile.
4. Add menu items via the Menu Items section.
5. Place the `[smooth_menu]` shortcode on any page to display your menu.
6. Place the `[smooth_reservation]` shortcode for table bookings.

== Frequently Asked Questions ==

= Does this plugin require WooCommerce? =

No. Smooth Restaurant is completely standalone. However, if you already have WooCommerce with payment gateways configured, you can optionally enable the WooCommerce payment bridge in settings.

= Can I use this for multiple restaurant locations? =

The free tier supports 1 location. Upgrade to Pro for unlimited locations.

= Does it support online payments? =

The free tier supports cash on delivery / pay at counter. Pro tier includes Stripe, PayPal, Square, and other payment gateways.

= Can I print orders automatically to my kitchen printer? =

Yes! The free tier includes thermal printer support via WebUSB (USB printers) and WebSocket proxy (network printers).

= Is there a mobile app? =

The staff dashboard and KDS are fully responsive and work great as web apps on tablets and phones. Add them to your home screen for an app-like experience.

== Screenshots ==

1. Restaurant dashboard — at-a-glance view of today's operations
2. Menu management — drag-and-drop category ordering
3. Order management — real-time order list with status workflow
4. Kitchen Display System — full-screen order cards for kitchen staff
5. Reservation management — calendar view with time slot availability
6. Staff dashboard — touch-optimized interface for non-technical staff

== Changelog ==

= 1.0.0 =
* Initial release
* Menu management with custom post types
* Standalone ordering system (no WooCommerce required)
* Reservation system with time slot generation
* Kitchen Display System (KDS)
* Thermal printer integration
* QR code ordering
* Staff-first interface
* Order tracking page
* Role-based access control

== Upgrade Notice ==

= 1.0.0 =
Initial release. Welcome to Smooth Restaurant!
