<!-- Updated: Blue-based palette to differentiate from competitors -->

---
name: "Smooth Restaurant"
description: "The restaurant management plugin that actually works for restaurant people."
colors:
  primary:
    displayName: "Deep Navy"
    role: "primary"
    value: "#1e40af"
  primary-dark:
    displayName: "Navy Dark"
    role: "primary"
    value: "#1e3a8a"
  primary-light:
    displayName: "Navy Light"
    role: "primary"
    value: "#3b82f6"
  neutral-bg:
    displayName: "Cool White"
    role: "neutral"
    value: "#f8fafc"
  neutral-surface:
    displayName: "Cloud Surface"
    role: "neutral"
    value: "#f1f5f9"
  neutral-border:
    displayName: "Cloud Border"
    role: "neutral"
    value: "#e2e8f0"
  neutral-text:
    displayName: "Midnight"
    role: "neutral"
    value: "#0f172a"
  neutral-text-secondary:
    displayName: "Cool Gray"
    role: "neutral"
    value: "#64748b"
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
    backgroundColor: "#1e3a8a"
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

The Smooth Restaurant admin panel is designed to feel like a well-organized kitchen pass during prep hours: everything in its place, nothing extra, ready for the rush. The aesthetic is calm, capable, and unobtrusive. We reject the overwhelming density of e-commerce admin panels and the generic SaaS look. Instead, we aim for the quiet confidence of tools like Linear and Stripe: information-rich but never noisy, dense when needed but always breathable.

This system uses a **Restrained** color strategy: tinted cool neutrals carry the surface, with a single deep navy accent reserved for primary actions and active states. The accent appears on no more than 10% of any screen. Its rarity is the point.

The type system inherits the WordPress system font stack for technical performance and native feel. No custom fonts, no loading penalties, no visual departure from the platform the user already knows.

**Key Characteristics:**
- Cool, grounded neutrals with a single purposeful accent
- System-native typography for zero loading overhead
- Flat surfaces with tonal layering for depth
- State-driven motion only: no decorative animation
- Dark mode as a first-class citizen, defaulting to system preference
- Touch-friendly minimum targets (44px) for tablet use at the host stand

## 2. Colors

The palette is built on cool slate neutrals with a single deep navy accent. The coolness creates a professional, trustworthy feel that differentiates us from competitor products using warm orange/amber tones.

### Primary
- **Deep Navy** (`#1e40af` / oklch(45% 0.18 260)): The sole accent. Used for primary buttons, active navigation items, links, and status indicators that demand attention. It appears sparingly; its rarity makes it effective.
- **Navy Dark** (`#1e3a8a`): Hover state for primary buttons.
- **Navy Light** (`#3b82f6`): Lighter variant for subtle accents and focus states.

### Neutral
- **Cool White** (`#f8fafc` / oklch(98% 0.002 250)): The primary content background. Slightly cool, never pure white. Used for cards, panels, and main surfaces.
- **Cloud Surface** (`#f1f5f9` / oklch(96% 0.002 250)): Secondary surface for sidebars, toolbars, and elevated panels. Slightly darker than Cool White for subtle layering.
- **Cloud Border** (`#e2e8f0` / oklch(90% 0.005 250)): Dividers, input borders, and separators. Low contrast by design.
- **Midnight** (`#0f172a` / oklch(20% 0.02 260)): Primary text. High contrast against Cool White. Cool-tinted, never pure black.
- **Cool Gray** (`#64748b` / oklch(55% 0.03 260)): Secondary text, placeholders, captions, and metadata.

### Semantic
- **Sage** (`#15803d`): Success states, confirmed orders, positive indicators.
- **Amber** (`#b45309`): Warning states, pending actions, items needing attention.
- **Crimson** (`#b91c1c`): Error states, cancelled orders, critical alerts.
- **Slate** (`#475569`): Informational states, neutral badges, tips.

### Dark Mode
Dark mode inverts the neutral stack while preserving the primary accent and semantic colors:
- Background: `#0f172a` (cool near-black, oklch(18% 0.02 260))
- Surface: `#1e293b` (dark slate becomes the surface)
- Text: `#f8fafc` (Cool White becomes primary text)
- Secondary text: `#94a3b8` (lightened Cool Gray)
- Borders: `#334155` (darkened Cloud Border)

**The One Accent Rule.** The primary accent (Deep Navy) is used on <=10% of any given screen. Its rarity is the point. If a screen feels "too blue," too much accent is being used. Rewrite with neutral buttons, neutral badges, or tonal backgrounds instead.

## 3. Typography

**Display/Headline/Title/Body/Label Font:** WordPress system stack (`-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif`)

**Character:** Native, fast, familiar. The system font stack ensures the admin panel feels like a natural extension of WordPress, not a foreign application. Weight and scale create hierarchy, not font choice.

### Hierarchy
- **Display** (600, 1.5rem/24px, line-height 1.2): Page titles. Used once per screen.
- **Headline** (600, 1.25rem/20px, line-height 1.3): Section headings, card titles.
- **Title** (600, 1rem/16px, line-height 1.4): Subsection headings, table column headers.
- **Body** (400, 0.875rem/14px, line-height 1.5): Primary reading text. Max line length 65-75ch.
- **Label** (500, 0.75rem/12px, line-height 1.4, letter-spacing 0.01em): Buttons, form labels, badges, metadata. Uppercase only for status badges and section labels.

**The One Family Rule.** Only the system font stack is used. No display fonts, no mono fonts for UI labels, no second family for contrast. Hierarchy is achieved through weight, size, and color, not font switching.

## 4. Elevation

This system is **flat by default, layered by purpose**. Surfaces rest flat on the background. Depth is conveyed through tonal shifts (Cool White -> Cloud Surface) rather than shadows. Shadows appear only as a response to state: hover elevation, focus rings, and modal overlays.

### Shadow Vocabulary
- **Ambient Hover** (`0 2px 8px rgba(0,0,0,0.06)`): Subtle lift on interactive cards and buttons during hover. Never persistent.
- **Focus Ring** (`0 0 0 3px rgba(30, 64, 175, 0.25)`): Deep Navy glow around focused interactive elements. Visible, cool, unmistakable.
- **Modal Overlay** (`0 8px 32px rgba(0,0,0,0.12)`): Elevated panels, dropdowns, and modals. The heaviest shadow in the system.

**The Flat-By-Default Rule.** Surfaces are flat at rest. Shadows appear only as a response to state (hover, elevation, focus). Persistent shadows on cards or panels are prohibited.

## 5. Components

### Buttons
- **Shape:** Gently rounded corners (8px radius)
- **Primary:** Deep Navy background (`#1e40af`), white text, 8px 16px padding. Used for the single most important action on a screen.
- **Hover / Focus:** Background darkens to `#1e3a8a`. Focus ring appears. Transition: 150ms ease-out.
- **Secondary:** Transparent background, Midnight text, 1px Cloud Border. Used for secondary actions and cancel buttons.
- **Ghost:** No background, no border, Deep Navy text. Used for tertiary actions and inline links.
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
- **Background:** Cool White (`#f8fafc`). In dark mode: `#0f172a`.
- **Shadow Strategy:** Flat at rest. Ambient hover shadow on interactive cards only.
- **Border:** 1px Cloud Border (`#e2e8f0`) for definition. In dark mode: `#334155`.
- **Internal Padding:** 24px (`spacing.lg`). Consistent across all cards.

### Inputs / Fields
- **Style:** 1px Cloud Border, Cool White background, 8px radius, 8px 12px padding.
- **Focus:** Border shifts to Deep Navy (`#1e40af`), focus ring appears. Background remains Cool White.
- **Error:** Border shifts to Crimson (`#b91c1c`), error text below in Crimson.
- **Disabled:** Background shifts to Cloud Surface, text to Cool Gray, border to Cloud Border. No interaction.

### Navigation
- **Style:** Sidebar navigation with icon + label. Cloud Surface background.
- **Default:** Midnight text, no background. 8px 12px padding, 8px radius.
- **Hover:** Cloud Surface background, Midnight text.
- **Active:** Cool White background, Deep Navy text, Deep Navy left border (2px, inside the item). This is the only permitted use of a side accent.
- **Mobile:** Collapses to a bottom tab bar on screens < 768px.

### Tables / Data Grids
- **Style:** No outer border. Row separators only (1px Cloud Border).
- **Header:** Cloud Surface background, Label typography (500, 0.75rem), uppercase, Cool Gray text.
- **Row:** Cool White background. Hover: Cloud Surface background.
- **Selected Row:** Cloud Surface background, Deep Navy left border (2px).
- **Cell Padding:** 12px 16px.

## 6. Do's and Don'ts

### Do:
- **Do** use the system font stack exclusively. No custom font loading.
- **Do** default to the user's system theme (light/dark) with a manual toggle available.
- **Do** use Deep Navy on <=10% of any screen. Its rarity is its power.
- **Do** use tonal layering (Cool White -> Cloud Surface) to create depth without shadows.
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
- **Don't** use pure black (`#000`) or pure white (`#fff`). All neutrals are tinted cool.
