---
name: Institutional Ecology
colors:
  surface: '#fbf9f8'
  surface-dim: '#dcd9d9'
  surface-bright: '#fbf9f8'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f6f3f2'
  surface-container: '#f0eded'
  surface-container-high: '#eae8e7'
  surface-container-highest: '#e4e2e1'
  on-surface: '#1b1c1c'
  on-surface-variant: '#414944'
  inverse-surface: '#303030'
  inverse-on-surface: '#f3f0f0'
  outline: '#717973'
  outline-variant: '#c0c9c2'
  surface-tint: '#3a6752'
  primary: '#134230'
  on-primary: '#ffffff'
  primary-container: '#2d5a46'
  on-primary-container: '#9fcfb6'
  inverse-primary: '#a1d1b8'
  secondary: '#006e2a'
  on-secondary: '#ffffff'
  secondary-container: '#5cfd80'
  on-secondary-container: '#00732c'
  tertiary: '#163a6c'
  on-tertiary: '#ffffff'
  tertiary-container: '#315284'
  on-tertiary-container: '#a7c6ff'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#bceed3'
  primary-fixed-dim: '#a1d1b8'
  on-primary-fixed: '#002114'
  on-primary-fixed-variant: '#224f3c'
  secondary-fixed: '#69ff87'
  secondary-fixed-dim: '#3ce36a'
  on-secondary-fixed: '#002108'
  on-secondary-fixed-variant: '#00531e'
  tertiary-fixed: '#d6e3ff'
  tertiary-fixed-dim: '#a9c7ff'
  on-tertiary-fixed: '#001b3d'
  on-tertiary-fixed-variant: '#254778'
  background: '#fbf9f8'
  on-background: '#1b1c1c'
  surface-variant: '#e4e2e1'
typography:
  headline-xl:
    fontFamily: Inter
    fontSize: 40px
    fontWeight: '700'
    lineHeight: 48px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Inter
    fontSize: 32px
    fontWeight: '600'
    lineHeight: 40px
    letterSpacing: -0.01em
  headline-md:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  headline-sm:
    fontFamily: Inter
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-sm:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 16px
  label-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
  headline-lg-mobile:
    fontFamily: Inter
    fontSize: 28px
    fontWeight: '600'
    lineHeight: 36px
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  base: 8px
  xs: 4px
  sm: 12px
  md: 24px
  lg: 48px
  xl: 80px
  gutter: 16px
  margin-mobile: 20px
  margin-desktop: auto
  max-width: 1200px
---

## Brand & Style

The brand personality is institutional, reliable, and environmentally conscious. It is designed to evoke a sense of civic responsibility and trust, positioning the service as a modern infrastructure essential for the Chiriquí region.

The visual style is **Corporate / Modern** with a focus on high legibility and structured information density. It utilizes a clean, card-based interface to organize data like collection routes and payment histories, ensuring the user feels a sense of order and efficiency. The "Institutional Ecology" aesthetic blends the organic tones of environmental services with the precise, functional layouts of a professional utility provider.

## Colors

The palette is rooted in the natural landscape of Panama. 

- **Primary Green:** Used for global navigation headers, primary buttons, and institutional branding to represent stability and growth.
- **Accent Green:** A high-visibility "vibrant" green reserved for key conversion points like "Create Account" or success states (e.g., "Pagado").
- **Tertiary Blue:** A deep navy used for the footer and specific map-related UI elements to provide contrast against the dominant green tones.
- **Neutrals:** The system uses a pure white background for maximum clarity, with "Fog Gray" secondary surfaces to define card boundaries and input fields without creating harsh visual breaks.

## Typography

This design system utilizes **Inter** exclusively to ensure a clean, professional, and highly legible experience across all platforms. 

- **Headlines:** Use Bold and Semi-Bold weights with tight letter-spacing to create a strong visual hierarchy.
- **Body Text:** Standardizes on a 16px base for optimal readability on mobile and desktop.
- **Labels:** Small labels utilize a medium weight and slight tracking to remain legible even at 12px.
- **Hierarchy:** H1 elements in the Primary Green color reinforce the brand identity within the content structure.

## Layout & Spacing

The layout follows a **Fixed Grid** model on desktop (1200px max-width) and a **Fluid** model on mobile devices. 

- **Grid:** A 12-column grid is used for desktop, collapsing to a single column on mobile. 
- **Rhythm:** The spacing system is based on an 8px scale. Use `md` (24px) for standard padding within cards and `lg` (48px) for vertical section spacing.
- **Margins:** Mobile screens should maintain a minimum safe margin of 20px on the left and right edges.

## Elevation & Depth

Visual hierarchy is achieved through a combination of **Tonal Layers** and **Ambient Shadows**.

- **Surfaces:** The primary background is white. Secondary areas (like the map info panel or inactive cards) use the light gray neutral (#E5EAE7) to create subtle depth without shadows.
- **Cards:** Active cards use a "Low-Contrast Outline" combined with an extra-diffused ambient shadow (Offset: 0, 4px; Blur: 20px; Opacity: 6% Black). This makes the information appear to lift slightly off the page.
- **Overlays:** Modals and dropdowns use a more pronounced shadow to separate them from the main task flow.

## Shapes

The shape language is consistently **Rounded**, using an 8px (0.5rem) base to convey friendliness and modern accessibility.

- **Base Radius:** 8px for standard buttons, input fields, and small cards.
- **Large Radius (rounded-lg):** 16px (1rem) for main container cards (e.g., Payment History, Profile Summary).
- **Extra Large Radius (rounded-xl):** 24px (1.5rem) for large image containers or decorative background elements.
- **Interactive Elements:** Checkboxes and radio buttons maintain a slight 4px rounding to match the system language.

## Components

### Buttons
- **Primary:** Solid #2D5A46 with white text, 8px radius.
- **Secondary (Action):** Solid #00C853 with white text. Reserved for "Create Account" or "Confirm Payment."
- **Ghost:** Primary Green border (2px) with transparent background and green text.

### Cards
- Standard cards use white backgrounds, an 8px radius, and a 1px border (#E5EAE7).
- Dashboard cards (e.g., "Next Payment") use a 16px radius and the subtle ambient shadow.

### Input Fields
- Background: #E5EAE7.
- Border: None by default, 2px Primary Green on focus.
- Placeholder Text: 50% opacity of the Neutral #333333.

### Chips/Status Indicators
- **Pagado/Activo:** Light green background with #00C853 text.
- **Pendiente:** Light yellow background with amber text.
- Pill-shaped (fully rounded) for all status indicators.

### Navigation
- Top navigation bar should be Primary Green with white text links.
- Footer uses the "Dark blue/black" variant (#1A2B4C) and contains the circular SACH logo, contact info, and social icons.

### Logos
- **Institutional Logo:** Hill circular logo on #2D5A46 background for headers.
- **Full Identity:** "smartSACH" on white background for landing pages and login screens.
- **Footer Brand:** Circular white SACH logo centered or left-aligned in the footer.