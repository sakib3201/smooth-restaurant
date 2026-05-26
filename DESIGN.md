<!-- SEED: re-run /impeccable document once there's code to capture the actual tokens and components. -->

---
name: "Smooth Restaurant"
description: "The restaurant management plugin that actually works for restaurant people."
colors:
  primary:
    displayName: "Warm Ember"
    role: "primary"
    value: "#c2410c"
  neutral-bg:
    displayName: "Warm White"
    role: "neutral"
    value: "#fafaf9"
  neutral-surface:
    displayName: "Stone Surface"
    role: "neutral"
    value: "#f5f5f4"
  neutral-border:
    displayName: "Stone Border"
    role: "neutral"
    value: "#e7e5e4"
  neutral-text:
    displayName: "Charcoal"
    role: "neutral"
    value: "#292524"
  neutral-text-secondary:
    displayName: "Warm Gray"
    role: "neutral"
    value: "#78716c"
  success:
    displayName: "Sage"
    role: "semantic"
    value: "#15803d"
  warning:
    displayName: "Amber"
    role: "semantic"
    value: "#b45309"
  error:
    displayName: "Crimson"
    role: "semantic"
    value: "#b91c1c"
  info:
    displayName: "Slate"
    role: "semantic"
    value: "#475569"
typography:
  display:
    fontFamily: "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif"
    fontSize: "1.5rem"
    fontWeight: 600
    lineHeight: 1.2
    letterSpacing: "-0.01em"
  headline:
    fontFamily: "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif"
    fontSize: "1.25rem"
    fontWeight: 600
    lineHeight: 1.3
    letterSpacing: "-0.005em"
  title:
    fontFamily: "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif"
    fontSize: "1rem"
    fontWeight: 600
    lineHeight: 1.4
    letterSpacing: "normal"
  body:
    fontFamily: "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif"
    fontSize: "0.875rem"
    fontWeight: 400
    lineHeight: 1.5
    letterSpacing: "normal"
  label:
    fontFamily: "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif"
    fontSize: "0.75rem"
    fontWeight: 500
    lineHeight: 1.4
    letterSpacing: "0.01em"
rounded:
  sm: "4px"
  md: "8px"
  lg: "12px"
  full: "9999px"
spacing:
  xs: "4px"
  sm: "8px"
  md: "16px"
  lg: "24px"
  xl: "32px"
  2xl: "48px"
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "#ffffff"
    rounded: "{rounded.md}"
    padding: "8px 16px"
    typography: "{typography.label}"
  button-primary-hover:
    backgroundColor: "#9a3412"
    textColor: "#ffffff"
  button-secondary:
    backgroundColor: "transparent"
    textColor: "{colors.neutral-text}"
    rounded: "{rounded.md}"
    padding: "8px 16px"
  button-secondary-hover:
    backgroundColor: "{colors.neutral-surface}"
    textColor: "{colors.neutral-text}"
  input-field:
    backgroundColor: "{colors.neutral-bg}"
    textColor: "{colors.neutral-text}"
    rounded: "{rounded.md}"
    padding: "8px 12px"
  input-field-focus:
    backgroundColor: "{colors.neutral-bg}"
    textColor: "{colors.neutral-text}"
  card:
    backgroundColor: "{colors.neutral-bg}"
    textColor: "{colors.neutral-text}"
    rounded: "{rounded.lg}"
    padding: "{spacing.lg}"
  nav-item-active:
    backgroundColor: "{colors.neutral-surface}"
    textColor: "{colors.primary}"
    rounded: "{rounded.md}"
    padding: "8px 12px"
---

# Design System: Smooth Restaurant

## 1. Overview

**Creative North Star: "The Calm Kitchen Pass"**

The Smooth Restaurant admin panel is designed to feel like a well-organized kitchen pass during prep hours: everything in its place, nothing extra, ready for the rush. The aesthetic is warm, capable, and unobtrusive. We reject the cold, clinical feel of generic SaaS dashboards and the overwhelming density of e-commerce admin panels. Instead, we aim for the quiet confidence of tools like Linear and Stripe: information-rich but never noisy, dense when needed but always breathable.

This system uses a **Restrained** color strategy: tinted warm neutrals carry the surface, with a single warm ember accent reserved for primary actions and active states. The accent appears on no more than 10% of any screen. Its rarity is the point.

The type system inherits the WordPress system font stack for technical performance and native feel. No custom fonts, no loading penalties, no visual departure from the platform the user already knows.

**Key Characteristics:**
- Warm, grounded neutrals with a single purposeful accent
- System-native typography for zero loading overhead
- Flat surfaces with tonal layering for depth
- State-driven motion only: no decorative animation
- Dark mode as a first-class citizen, defaulting to system preference
- Touch-friendly minimum targets (44px) for tablet use at the host stand

## 2. Colors

The palette is built on warm stone neutrals with a single ember accent. The warmth comes from subtle hue tinting toward orange (the brand's primary), creating cohesion without saturation.

### Primary
- **Warm Ember** (`#c2410c` / oklch(55% 0.18 45)): The sole accent. Used for primary buttons, active navigation items, links, and status indicators that demand attention. It appears sparingly; its rarity makes it effective.

### Neutral
- **Warm White** (`#fafaf9` / oklch(98% 0.002 65)): The primary content background. Slightly warm, never pure white. Used for cards, panels, and main surfaces.
- **Stone Surface** (`#f5f5f4` / oklch(96% 0.002 65)): Secondary surface for sidebars, toolbars, and elevated panels. Slightly darker than Warm White for subtle layering.
- **Stone Border** (`#e7e5e4` / oklch(90% 0.005 65)): Dividers, input borders, and separators. Low contrast by design.
- **Charcoal** (`#292524` / oklch(25% 0.01 65)): Primary text. High contrast against Warm White. Warm-tinted, never pure black.
- **Warm Gray** (`#78716c` / oklch(55% 0.01 65)): Secondary text, placeholders, captions, and metadata.

### Semantic
- **Sage** (`#15803d`): Success states, confirmed orders, positive indicators.
- **Amber** (`#b45309`): Warning states, pending actions, items needing attention.
- **Crimson** (`#b91c1c`): Error states, cancelled orders, critical alerts.
- **Slate** (`#475569`): Informational states, neutral badges, tips.

### Dark Mode
Dark mode inverts the neutral stack while preserving the primary accent and semantic colors:
- Background: `#1c1917` (warm near-black, oklch(18% 0.01 65))
- Surface: `#292524` (Charcoal becomes the surface)
- Text: `#fafaf9` (Warm White becomes primary text)
- Secondary text: `#a8a29e` (lightened Warm Gray)
- Borders: `#44403c` (darkened Stone Border)

**The One Accent Rule.** The primary accent (Warm Ember) is used on ≤10% of any given screen. Its rarity is the point. If a screen feels "orange," too much accent is being used. Rewrite with neutral buttons, neutral badges, or tonal backgrounds instead.

## 3. Typography

**Display/Headline/Title/Body/Label Font:** WordPress system stack (`-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif`)

**Character:** Native, fast, familiar. The system font stack ensures the admin panel feels like a natural extension of WordPress, not a foreign application. Weight and scale create hierarchy, not font choice.

### Hierarchy
- **Display** (600, 1.5rem/24px, line-height 1.2): Page titles. Used once per screen.
- **Headline** (600, 1.25rem/20px, line-height 1.3): Section headings, card titles.
- **Title** (600, 1rem/16px, line-height 1.4): Subsection headings, table column headers.
- **Body** (400, 0.875rem/14px, line-height 1.5): Primary reading text. Max line length 65–75ch.
- **Label** (500, 0.75rem/12px, line-height 1.4, letter-spacing 0.01em): Buttons, form labels, badges, metadata. Uppercase only for status badges and section labels.

**The One Family Rule.** Only the system font stack is used. No display fonts, no mono fonts for UI labels, no second family for contrast. Hierarchy is achieved through weight, size, and color, not font switching.

## 4. Elevation

This system is **flat by default, layered by purpose**. Surfaces rest flat on the background. Depth is conveyed through tonal shifts (Warm White → Stone Surface) rather than shadows. Shadows appear only as a response to state: hover elevation, focus rings, and modal overlays.

### Shadow Vocabulary
- **Ambient Hover** (`0 2px 8px rgba(0,0,0,0.06)`): Subtle lift on interactive cards and buttons during hover. Never persistent.
- **Focus Ring** (`0 0 0 3px rgba(194, 65, 12, 0.25)`): Warm Ember glow around focused interactive elements. Visible, warm, unmistakable.
- **Modal Overlay** (`0 8px 32px rgba(0,0,0,0.12)`): Elevated panels, dropdowns, and modals. The heaviest shadow in the system.

**The Flat-By-Default Rule.** Surfaces are flat at rest. Shadows appear only as a response to state (hover, elevation, focus). Persistent shadows on cards or panels are prohibited.

## 5. Components

### Buttons
- **Shape:** Gently rounded corners (8px radius)
- **Primary:** Warm Ember background (`#c2410c`), white text, 8px 16px padding. Used for the single most important action on a screen.
- **Hover / Focus:** Background darkens to `#9a3412`. Focus ring appears. Transition: 150ms ease-out.
- **Secondary:** Transparent background, Charcoal text, 1px Stone Border. Used for secondary actions and cancel buttons.
- **Ghost:** No background, no border, Warm Ember text. Used for tertiary actions and inline links.
- **Danger:** Crimson background (`#b91c1c`), white text. Used for destructive actions (delete, cancel order).

### Status Badges / Chips
- **Shape:** Fully rounded (9999px radius), pill form
- **Style:** Background is a 10% opacity tint of the semantic color. Text is the full semantic color.
  - Success: `bg-green-100 text-green-800` (light) / `bg-green-900/30 text-green-400` (dark)
  - Warning: `bg-amber-100 text-amber-800` (light) / `bg-amber-900/30 text-amber-400` (dark)
  - Error: `bg-red-100 text-red-800` (light) / `bg-red-900/30 text-red-400` (dark)
  - Info: `bg-slate-100 text-slate-800` (light) / `bg-slate-900/30 text-slate-400` (dark)
- **State:** No hover state. Badges are informational, not interactive.

### Cards / Containers
- **Corner Style:** 12px radius. Slightly more rounded than buttons to distinguish container from control.
- **Background:** Warm White (`#fafaf9`). In dark mode: `#1c1917`.
- **Shadow Strategy:** Flat at rest. Ambient hover shadow on interactive cards only.
- **Border:** 1px Stone Border (`#e7e5e4`) for definition. In dark mode: `#44403c`.
- **Internal Padding:** 24px (`spacing.lg`). Consistent across all cards.

### Inputs / Fields
- **Style:** 1px Stone Border, Warm White background, 8px radius, 8px 12px padding.
- **Focus:** Border shifts to Warm Ember (`#c2410c`), focus ring appears. Background remains Warm White.
- **Error:** Border shifts to Crimson (`#b91c1c`), error text below in Crimson.
- **Disabled:** Background shifts to Stone Surface, text to Warm Gray, border to Stone Border. No interaction.

### Navigation
- **Style:** Sidebar navigation with icon + label. Stone Surface background.
- **Default:** Charcoal text, no background. 8px 12px padding, 8px radius.
- **Hover:** Stone Surface background, Charcoal text.
- **Active:** Stone Surface background, Warm Ember text, Warm Ember left border (2px, inside the item). This is the only permitted use of a side accent.
- **Mobile:** Collapses to a bottom tab bar on screens < 768px.

### Tables / Data Grids
- **Style:** No outer border. Row separators only (1px Stone Border).
- **Header:** Stone Surface background, Label typography (500, 0.75rem), uppercase, Warm Gray text.
- **Row:** Warm White background. Hover: Stone Surface background.
- **Selected Row:** Stone Surface background, Warm Ember left border (2px).
- **Cell Padding:** 12px 16px.

## 6. Do's and Don'ts

### Do:
- **Do** use the system font stack exclusively. No custom font loading.
- **Do** default to the user's system theme (light/dark) with a manual toggle available.
- **Do** use Warm Ember on ≤10% of any screen. Its rarity is its power.
- **Do** use tonal layering (Warm White → Stone Surface) to create depth without shadows.
- **Do** ensure all interactive elements have a minimum 44px tap target for tablet use.
- **Do** use both color and icon/shape for status indicators. Never rely on color alone.
- **Do** respect `prefers-reduced-motion`. All transitions should be instant for users who request it.
- **Do** use sentence case for all labels, headings, and buttons. No ALL CAPS except for status badges and section labels.

### Don't:
- **Don't** load custom fonts or icon fonts. Use inline SVGs and the system stack.
- **Don't** use gradient text (`background-clip: text`). Use a single solid color for all text.
- **Don't** use glassmorphism (backdrop-filter blur) for any surface. It reduces readability and feels decorative.
- **Don't** use side-stripe borders (colored left/right borders > 1px) on cards, lists, or alerts. The only permitted side accent is the 2px active indicator in navigation and selected table rows.
- **Don't** use the hero-metric template (big number, small label, gradient accent, supporting stats). Present data in context, not as decoration.
- **Don't** create identical card grids (icon + heading + text, repeated endlessly). Vary card content and layout.
- **Don't** use modals as a first thought for every action. Exhaust inline, progressive, and slide-out alternatives first.
- **Don't** use em dashes. Use commas, colons, semicolons, periods, or parentheses.
- **Don't** design like WooCommerce admin. Avoid dense forms, overwhelming sidebars, and e-commerce-centric layouts.
- **Don't** use decorative motion that doesn't convey state. No bouncing loaders, no elastic transitions, no choreographed page entrances.
- **Don't** use pure black (`#000`) or pure white (`#fff`). All neutrals are tinted warm.
