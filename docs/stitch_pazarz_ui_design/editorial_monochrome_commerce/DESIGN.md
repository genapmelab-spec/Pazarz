---
name: Editorial Monochrome Commerce
colors:
  surface: '#f9f9f7'
  surface-dim: '#dadad8'
  surface-bright: '#f9f9f7'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f4f4f2'
  surface-container: '#eeeeec'
  surface-container-high: '#e8e8e6'
  surface-container-highest: '#e2e3e1'
  on-surface: '#1a1c1b'
  on-surface-variant: '#444748'
  inverse-surface: '#2f3130'
  inverse-on-surface: '#f1f1ef'
  outline: '#747878'
  outline-variant: '#c4c7c7'
  surface-tint: '#5f5e5e'
  primary: '#000000'
  on-primary: '#ffffff'
  primary-container: '#1c1b1b'
  on-primary-container: '#858383'
  inverse-primary: '#c8c6c5'
  secondary: '#5e5e5e'
  on-secondary: '#ffffff'
  secondary-container: '#e4e2e2'
  on-secondary-container: '#646464'
  tertiary: '#000000'
  on-tertiary: '#ffffff'
  tertiary-container: '#001847'
  on-tertiary-container: '#427dfc'
  error: '#D8362B'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#e5e2e1'
  primary-fixed-dim: '#c8c6c5'
  on-primary-fixed: '#1c1b1b'
  on-primary-fixed-variant: '#474646'
  secondary-fixed: '#e4e2e2'
  secondary-fixed-dim: '#c8c6c6'
  on-secondary-fixed: '#1b1c1c'
  on-secondary-fixed-variant: '#474747'
  tertiary-fixed: '#dae2ff'
  tertiary-fixed-dim: '#b1c5ff'
  on-tertiary-fixed: '#001847'
  on-tertiary-fixed-variant: '#0040a0'
  background: '#FFFFFF'
  on-background: '#1a1c1b'
  surface-variant: '#e2e3e1'
  surface-elevated: '#FFFFFF'
  text-primary: '#111111'
  text-secondary: '#5E5E5E'
  text-muted: '#9A9A9A'
  border: '#E4E4E1'
  divider: '#EDEDEA'
  success: '#1E8E5A'
  warning: '#B98900'
  info: '#2F6FED'
  dark-background: '#0E0E0E'
  dark-surface: '#171717'
  dark-surface-elevated: '#1F1F1F'
typography:
  display:
    fontFamily: Inter
    fontSize: 64px
    fontWeight: '700'
    lineHeight: '1.05'
    letterSpacing: -0.02em
  display-mobile:
    fontFamily: Inter
    fontSize: 40px
    fontWeight: '700'
    lineHeight: '1.05'
    letterSpacing: -0.02em
  h1:
    fontFamily: Inter
    fontSize: 40px
    fontWeight: '700'
    lineHeight: '1.15'
    letterSpacing: -0.01em
  h1-mobile:
    fontFamily: Inter
    fontSize: 28px
    fontWeight: '700'
    lineHeight: '1.15'
    letterSpacing: -0.01em
  h2:
    fontFamily: Inter
    fontSize: 32px
    fontWeight: '600'
    lineHeight: '1.2'
    letterSpacing: -0.01em
  h3:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: '1.25'
    letterSpacing: '0'
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: '1.5'
    letterSpacing: '0'
  body:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: '1.5'
    letterSpacing: '0'
  label:
    fontFamily: Inter
    fontSize: 13px
    fontWeight: '600'
    lineHeight: '1.3'
    letterSpacing: 0.02em
  caption:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '400'
    lineHeight: '1.35'
    letterSpacing: '0'
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  base: 4px
  xs: 4px
  sm: 8px
  md: 16px
  lg: 24px
  xl: 48px
  section-customer: 64px
  section-dashboard: 32px
  hero-v: 120px
  gutter-customer: 24px
  gutter-dashboard: 20px
---

## Brand & Style

This design system follows an **Editorial Monochrome** aesthetic, positioning the marketplace as a premium curator rather than a discount-driven platform. The brand personality is confident, refined, and quietly luxurious, prioritizing clarity and high-end typography over decorative elements.

The visual language is characterized by:
- **High-Contrast Minimalism:** A strict monochrome base where color is used exclusively for functional feedback or to highlight product photography.
- **Typographic Authority:** Heavy reliance on bold, large-scale typography to create a rhythmic hierarchy similar to a fashion magazine or high-end editorial.
- **Generous Whitespace:** Using space as a structural element to reduce cognitive load and evoke a sense of "breathing room" and quality.
- **Soft Geometry:** The starkness of the black-and-white palette is balanced by large, approachable border radii (16px–24px), creating a modern and "human" digital experience.

## Colors

The system uses a high-contrast monochrome palette. Color is reserved as a utility for status indication, interactive focus, and photography.

- **Primary & Neutral:** The foundation is built on `#111111` for high-impact elements and `#FFFFFF` for the environment.
- **Functional Accents:** `#2F6FED` (Blue) is the singular interactive accent used for focus states and text links.
- **Semantic Status:** 
    - **Success (#1E8E5A):** Paid, Delivered, Verified.
    - **Warning (#B98900):** Pending, Low Stock, Processing.
    - **Error (#D8362B):** Cancelled, Dispute, Failed.
- **Dark Mode:** Primarily utilized for the Seller and Admin Dashboards to reduce eye strain during long-tail operational tasks. In Dark Mode, depth is communicated through rising surface brightness rather than traditional shadows.

## Typography

The design system uses **Inter** exclusively to ensure a modern, neutral, and highly legible experience across all surfaces.

- **Display Hierarchy:** Limit "Display" text to one per page (typically the Hero section) to maintain editorial impact.
- **Editorial Contrast:** Use `Display` and `H1` with tight line heights and negative letter spacing to create a dense, "ink-on-paper" feel.
- **Functional Clarity:** Labels use a semi-bold weight and slight letter spacing for maximum readability at small sizes in dense dashboard environments.
- **Responsive Scaling:** Headline sizes significantly reduce on mobile to maintain layout integrity without breaking words awkwardly.

## Layout & Spacing

The system is built on a **4px base grid** to ensure mathematical harmony across all components.

- **Customer Layout:** Uses a **Fixed Grid** of 1280px maximum width. It utilizes a 12-column structure with 24px gutters. Margin-heavy layouts are preferred to enhance the premium feel.
- **Dashboard Layout (Seller/Admin):** Uses a **Fluid Grid** to accommodate data-heavy tables. It features a fixed 260px sidebar (collapsible to 72px) and tighter 20px gutters.
- **Spacing Rhythm:** 
    - Use `section-customer` (64px) for vertical gaps between product blocks on the storefront.
    - Use `section-dashboard` (32px) for page padding in internal management tools.
    - Use `hero-v` (120px) for the main landing hero to create maximum editorial impact.

## Elevation & Depth

This system avoids heavy drop shadows, relying instead on **Tonal Layers** and subtle ambient depth to indicate interactivity.

- **Flat Foundation:** The primary background is always `#FFFFFF` (Light) or `#0E0E0E` (Dark).
- **Surface Tiers:** Use `#F7F7F5` for secondary regions like sidebars or table headers to create separation without borders.
- **Subtle Elevation:** For floating elements like Modals, Dropdowns, and hoverable Product Cards, use a single, extremely diffused shadow (`elevation-1`).
- **Interactive Depth:** On hover, cards should subtly scale (`scale(1.02)`) and increase their shadow prominence.
- **Scrim:** Use a 40% black overlay (`rgba(0,0,0,0.4)`) for background dimming when drawers or modals are active.

## Shapes

The shape language creates a "Soft High-Contrast" look. Large, friendly radii contrast against the stark monochrome colors.

- **Pill Shape (999px):** Applied to primary action buttons, status badges, and the main customer search bar to emphasize "friendliness" and ease of use.
- **Container Radii:** 
    - `16px - 24px` for high-level containers like Product Cards, Modals, and Metric Cards.
    - `10px - 12px` for operational components like Inputs and Table containers.
- **Identity Distinction:** User avatars are always circular (Full radius), while Shop/Brand logos use a 12px radius square to differentiate "Personal" vs "Commercial" identities.

## Components

### Buttons
- **Primary:** Black background (`#111111`), White text, Pill-shaped. Hover: 8% darken. Active: 12% darken + `scale(0.98)`.
- **Secondary:** Bordered (`1px #E4E4E1`), Black text, Pill-shaped.
- **Ghost:** No background/border, Black text. Used for "Cancel" or "Reset" actions.

### Cards
- **Product Card:** 16px radius, no border. Uses a subtle `elevation-1` only on hover. Aspect ratio of imagery should be 3:4 or 1:1.
- **Metric Card:** 16px radius, `#F7F7F5` background, no shadow. Used for dashboard statistics.

### Inputs & Forms
- **Standard Input:** 10px radius, `#E4E4E1` border.
- **Focus State:** 2px accent border (`#2F6FED`) with a 2px offset ring.
- **Validation:** Error text should appear immediately below the field in `#D8362B`.

### Status Badges
- Small, pill-shaped tags with low-saturation backgrounds and high-saturation text.
- **Success:** Green theme for "Paid" or "Delivered".
- **Warning:** Amber theme for "Pending" or "Low Stock".

### Data Tables
- Header uses `#F7F7F5` background with `label` typography.
- Rows separated by `#EDEDEA` 1px dividers.
- Row hover state: 2% darken of the surface color.

### Feedback
- **Toasts:** Fixed bottom-center, 12px radius, auto-dismiss after 4 seconds.
- **Skeletons:** Shimmer animation must match the radius of the component it replaces (e.g., 16px for card skeletons).