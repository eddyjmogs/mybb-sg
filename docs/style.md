# Konoha Forum — Design System
**Crystal Edition · 木ノ葉隠れの里**

---

## Overview

Two themes, one design language. Both share identical layout structure, typography, spacing, and component shapes. Color values are the only thing that changes between them.

Three core principles:

- **Orange as fire** — `#c87830` is the single dominant warm accent. Everything else is neutral, making orange unmistakable wherever it appears.
- **Category color system** — five secondary colors (rose, lavender, teal, sage, and neutral gray) map directly to content categories. Each category owns its color across badges, thread accents, sidebar gems, tags, and mission icons.
- **Parchment texture** — both themes use stacked SVG `fractalNoise` filters to simulate paper grain. Light uses `multiply` blend mode, dark uses `screen` blend mode.
- **Konoha spiral** — the Konoha spiral symbol appears as a background watermark grid, as badge icons, as ornament separator elements, and as per-thread stamps in the bottom-right corner.

---

## Color System

### Category → Color Mapping

This mapping is the backbone of the design. Every category-colored element traces back to this table.

| Category | Light color | Dark color | Used in |
|----------|------------|-----------|---------|
| All / active | `#c87830` orange | `#d08838` orange | Active nav, logo, XP labels, new button |
| Debates | `#a87070` rose | `#c89898` rose | Badge, thread accent, sidebar gem, tag |
| Teorías | `#7068a8` lavender | `#9088b8` lavender | Badge, thread accent, sidebar gem, tag |
| Análisis | `#508080` teal | `#6aa0a8` teal | Badge, thread accent, sidebar gem, tag |
| Bienvenida | `#5a7850` sage | `#78a868` sage | Badge, thread accent, sidebar gem, tag |
| Arte / misc | neutral gray | neutral gray | Border only, no color fill |

### Village → Color Mapping

| Village | Color |
|---------|-------|
| Konoha | sage |
| Kiri | teal |
| Iwa | rose |
| Suna | orange |
| Kumo | lavender |

---

## Light Theme — Warm Gray

Background: `#f0ede8` (warm gray with slight beige tint)

### Color Tokens

```css
:root {
  /* Surfaces */
  --bg:          #f0ede8;   /* center background */
  --bg-side:     #d8d5d0;   /* sidebar background */
  --bg-side2:    #ccc9c4;   /* sidebar hover / bar tracks */
  --panel:       #f8f6f2;   /* thread card / raised surface */
  --panel2:      #edeae4;   /* secondary surface, stat blocks */
  --panel3:      #e0ddd8;   /* depressed surface */

  /* Borders */
  --border:      #c0bdb8;
  --border2:     #a09d98;   /* emphasis border, sidebar separator */

  /* Orange — primary accent */
  --orange:      #c87830;
  --orange-soft: #faf0e0;
  --orange-dim:  #d8a868;

  /* Rose — debates */
  --rose:        #a87070;
  --rose-soft:   #f8eee8;
  --rose-dim:    #d0a898;

  /* Lavender — teorías */
  --lav:         #7068a8;
  --lav-soft:    #eeeaf8;
  --lav-dim:     #b0a8d8;

  /* Teal — análisis */
  --teal:        #508080;
  --teal-soft:   #e4f2f0;
  --teal-dim:    #98c8c4;

  /* Sage — bienvenida, online dot, Konoha */
  --sage:        #5a7850;
  --sage-soft:   #e8f4e0;
  --sage-dim:    #a8c898;

  /* Ink — text hierarchy */
  --ink:         #1e1c18;
  --ink2:        #3a3830;
  --ink3:        #706a60;
  --ink4:        #a09890;
  --ink5:        #c8c4bc;
}
```

### Background Texture

```svg
<!-- Fine grain -->
<filter id="pf-fine">
  <feTurbulence type="fractalNoise" baseFrequency="0.68 0.72"
    numOctaves="5" seed="3" stitchTiles="stitch" result="n"/>
  <feColorMatrix in="n" type="saturate" values="0" result="g"/>
  <feComponentTransfer in="g" result="out">
    <feFuncR type="linear" slope="0.038" intercept="0.920"/>
    <feFuncG type="linear" slope="0.034" intercept="0.916"/>
    <feFuncB type="linear" slope="0.030" intercept="0.910"/>
    <feFuncA type="linear" slope="1"/>
  </feComponentTransfer>
  <feBlend in="SourceGraphic" in2="out" mode="multiply"/>
</filter>

<!-- Horizontal fibers -->
<filter id="pf-fiber">
  <feTurbulence type="fractalNoise" baseFrequency="0.006 0.88"
    numOctaves="2" seed="9" stitchTiles="stitch" result="f"/>
  <feColorMatrix in="f" type="saturate" values="0" result="gf"/>
  <feComponentTransfer in="gf" result="out">
    <feFuncA type="linear" slope="0.022"/>
  </feComponentTransfer>
  <feBlend in="SourceGraphic" in2="out" mode="multiply"/>
</filter>
```

Additional overlay elements:
- Ruling lines: `#b8b4ac` at `opacity: 0.20`, every 88px
- Warm top-right glow: orange `#e09050` radial at 10% opacity, 20% radius
- Spiral watermarks: 62px / 48px alternating, gray `#908880` / `#a09890`, opacity 4.2% / 3.0%

### Badge Colors (light)

| Category | Border | Background | Text |
|----------|--------|------------|------|
| Orange | `--orange-dim` | `--orange-soft` | `--orange` |
| Rose | `--rose-dim` | `--rose-soft` | `--rose` |
| Lavender | `--lav-dim` | `--lav-soft` | `--lav` |
| Teal | `--teal-dim` | `--teal-soft` | `--teal` |
| Sage | `--sage-dim` | `--sage-soft` | `--sage` |

### Special Badges (light)

| Badge | Style |
|-------|-------|
| `▲ ardiente` | 2px border `--orange`, color `--orange`, font-weight 500 |
| `fijado` | 1px border `--lav-dim`, background `--lav-soft`, color `--lav` |

---

## Dark Theme — Violet Night

Background: `#100e18` (deep violet-black)

The same category color system applies. Orange remains the primary accent — against the dark background it reads as torch fire. Category colors are brightened to maintain legibility on dark surfaces.

### Color Tokens

```css
:root {
  /* Surfaces */
  --bg:          #100e18;
  --bg-side:     #0a0810;
  --bg-side2:    #080610;
  --panel:       #1a1828;
  --panel2:      #201e32;
  --panel3:      #16142a;

  /* Borders */
  --border:      #2e2848;
  --border2:     #4a4478;

  /* Orange — torch fire */
  --orange:      #d08838;
  --orange-soft: #2a1808;
  --orange-dim:  #7a4820;
  --orange-glow: rgba(200,120,40,.15);

  /* Rose — debates */
  --rose:        #c89898;
  --rose-soft:   #200e0e;
  --rose-dim:    #602828;

  /* Lavender — teorías */
  --lav:         #9088b8;
  --lav-soft:    #1a1630;
  --lav-dim:     #4a4468;

  /* Teal — análisis */
  --teal:        #6aa0a8;
  --teal-soft:   #0a1820;
  --teal-dim:    #184858;

  /* Sage — bienvenida, online dot, Konoha */
  --sage:        #78a868;
  --sage-soft:   #0e1808;
  --sage-dim:    #2a4818;

  /* Text hierarchy */
  --text:        #e8e4f8;   /* primary */
  --text-mid:    #9088b8;   /* secondary */
  --text-dim:    #5a5488;   /* muted / labels */
}
```

### Background Texture

```svg
<!-- Fine grain — screen mode for dark bg -->
<filter id="df-fine">
  <feTurbulence type="fractalNoise" baseFrequency="0.68 0.72"
    numOctaves="5" seed="6" stitchTiles="stitch" result="n"/>
  <feColorMatrix in="n" type="saturate" values="0" result="g"/>
  <feComponentTransfer in="g" result="out">
    <feFuncR type="linear" slope="0.06" intercept="0.04"/>
    <feFuncG type="linear" slope="0.05" intercept="0.03"/>
    <feFuncB type="linear" slope="0.08" intercept="0.06"/>
    <feFuncA type="linear" slope="1"/>
  </feComponentTransfer>
  <feBlend in="SourceGraphic" in2="out" mode="screen"/>
</filter>

<!-- Horizontal fibers — screen mode -->
<filter id="df-fiber">
  <feTurbulence type="fractalNoise" baseFrequency="0.006 0.9"
    numOctaves="2" seed="13" stitchTiles="stitch" result="f"/>
  <feColorMatrix in="f" type="saturate" values="0" result="gf"/>
  <feComponentTransfer in="gf" result="out">
    <feFuncA type="linear" slope="0.04"/>
  </feComponentTransfer>
  <feBlend in="SourceGraphic" in2="out" mode="screen"/>
</filter>
```

Additional overlay elements:
- Ruling lines: `#2e2848` at `opacity: 0.50`, every 88px
- Moon glow top-right: `#c0b0e8` radial at 12% opacity, 26% radius
- Torch glow top-left: `#d08838` radial at 8% opacity, 18% radius
- Deep vignette: `#050408` at 50% opacity toward edges
- Spiral watermarks: lavender `#6058a8` / `#7068b8`, opacity **9.0% / 7.0%**

### Badge Colors (dark)

| Category | Border | Background | Text |
|----------|--------|------------|------|
| Orange | `--orange-dim` | `--orange-soft` | `--orange` |
| Rose | `--rose-dim` | `--rose-soft` | `--rose` |
| Lavender | `--lav-dim` | `--lav-soft` | `--lav` |
| Teal | `--teal-dim` | `--teal-soft` | `--teal` |
| Sage | `--sage-dim` | `--sage-soft` | `--sage` |

### Special Badges (dark)

| Badge | Style |
|-------|-------|
| `▲ ardiente` | 2px border `--orange`, color `--orange`, `box-shadow: 0 0 8px --orange-glow` |
| `fijado` | 1px border `--lav-dim`, background `--lav-soft`, color `--lav` |

---

## Typography

| Role | Family | Size | Weight | Notes |
|------|--------|------|--------|-------|
| Logo | `Cinzel` | 16px | 600 | Letter-spacing 0.10em |
| Nav items | `Cinzel` | 10px | 400–500 | Active: orange, weight 500 |
| Section labels | `Cinzel` | 8px | 400 | Uppercase, letter-spacing 0.22em |
| Category / gem names | `Cinzel` | 10px | 400 | Letter-spacing 0.06em |
| Badge text | `Cinzel` | 8px | 400 | Letter-spacing 0.06em |
| Thread titles | `Crimson Pro` | 15px | 400 | Italic, line-height 1.45 |
| Thread preview | `Crimson Pro` | 12px | 300 | Line-height 1.6, line-clamp 2 |
| Sublogo / JP text | `Noto Serif JP` | 9px | 300 | Letter-spacing 0.18em |
| All numeric values | `DM Mono` | 9–10px | 400–500 | Stats, counts, timestamps, XP |
| Author names | `Cinzel` | 9px | 400 | Letter-spacing 0.04em |

---

## Layout

```
┌──────────────────────────────────────────────────┐
│  HEADER  logo · nav centered · online status     │
│  ORNAMENT  spiral divider row                    │
├───────────┬─────────────────────────┬────────────┤
│  LEFT     │        MAIN             │  RIGHT     │
│  200px    │       flex 1fr          │  192px     │
│  darker   │    lightest zone        │  darker    │
└───────────┴─────────────────────────┴────────────┘
```

Left and right sidebars use **Option C contrast hierarchy** — noticeably darker than the center. A 2px border between zones reinforces the separation.

- **Left sidebar**: categories menu · community stats · village power bars
- **Right sidebar**: recent threads list · tag cloud · daily missions

---

## Spacing

| Token | Value |
|-------|-------|
| Page horizontal padding | 24px |
| Section vertical padding | 18px |
| Between sections (left sidebar) | 20px margin-bottom |
| Component inner padding | 10–12px |
| Thread inner padding | 12px vertical / 14px horizontal |
| Gap between threads | 10px |
| Sidebar item gap | 5–6px |
| Sidebar gem size | 8×8px, `transform: rotate(45deg)` |

---

## Components

### Thread Card

```
┌────────────────────────────────────────────┐  ← 1px --border
│▓▓▓▓▓▓▓░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░│  ← accent 3px gradient
│  [gem] category    [▲ ardiente]            │
│                                            │
│  Thread title italic Crimson Pro 15px      │
│  Two-line preview, line-clamped to 2       │
│ ─────────────────────────────────────────  │  ← 1px border-top
│  [av] author   N resp | K lecturas  time  +XP  │
└────────────────────────────────────────────┘
                             ↑ spiral stamp 72px, bottom-right
```

- Accent bar: 3px, `linear-gradient(90deg, color, color-dim, transparent)`
- Spiral stamp: 72×72px, absolute positioned bottom-right, opacity 6% light / 8% dark
- Hover: border upgrades to `--border2`
- Preview always clamps to exactly 2 lines

### Thread Accent → Category Color

| Category | Accent gradient start |
|----------|-----------------------|
| Debate (orange/featured) | `--orange` |
| Rose (debates) | `--rose` |
| Lavender (teorías) | `--lav` |
| Teal (análisis) | `--teal` |
| Sage (bienvenida) | `--sage` |

### Sidebar Category Gem

- 8×8px diamond (`transform: rotate(45deg)`)
- Color matches category: orange for "all/active", then rose/lav/teal/sage/gray
- Active menu item: border `--border2`, background `--panel`, text `--orange`

### Village Power Bar

- Track height: 5px
- Track: `--bg-side2`
- Fill: village color per mapping table above

### Online Dot

- 7×7px circle, sage fill, sage-dim border
- Opacity pulse animation: 2.5s, 40–100%

### Ornament Row

```
───── ◇ ◆ spiral ◆ spiral ◆ ◇ ─────
```
Structure: `line · sm-diamond · spiral(10px) · diamond · spiral(10px) · sm-diamond · line`

---

## Grain Filter Rules

```
Background dark (luminance < 30%)?
  YES → mode="screen",   slope ~0.05–0.08, intercept ~0.03–0.06
  NO  → mode="multiply", slope ~0.03–0.04, intercept ~0.91–0.93

Background warm tint (beige/gray)?
  → R and G intercepts slightly higher than B

Background violet/indigo tint?
  → B intercept slightly higher than R and G

Fiber filter always uses:
  baseFrequency="0.006 0.88"  (very low X = horizontal lines)
  feFuncA slope: 0.022 (light) / 0.040 (dark)
```

---

## Spiral Watermark Grid

Spirals tile in a brick pattern — odd and even rows offset by roughly half the column width.

| Property | Light | Dark |
|----------|-------|------|
| Large size | 62px | 62px |
| Small size | 48px | 48px |
| Large opacity | 4.2% | 9.0% |
| Small opacity | 3.0% | 7.0% |
| Color A (large) | `#908880` gray | `#6058a8` lavender |
| Color B (small) | `#a09890` gray | `#7068b8` lavender |
| Row spacing | ~88px | ~88px |
| Column spacing | ~190px | ~190px |

---

## Theme Switching

Replace only the `:root` block and the background SVG base fill. Everything else stays identical.

```css
/* Light */
:root {
  --bg: #f0ede8;
  --bg-side: #d8d5d0;
  --panel: #f8f6f2;
  --border: #c0bdb8;
  --border2: #a09d98;
  --orange: #c87830;
  --rose: #a87070;
  --lav: #7068a8;
  --teal: #508080;
  --sage: #5a7850;
  --ink: #1e1c18;
  /* full token set in Light section above */
}

/* Dark */
:root {
  --bg: #100e18;
  --bg-side: #0a0810;
  --panel: #1a1828;
  --border: #2e2848;
  --border2: #4a4478;
  --orange: #d08838;
  --rose: #c89898;
  --lav: #9088b8;
  --teal: #6aa0a8;
  --sage: #78a868;
  --ink: #e8e4f8;
  /* full token set in Dark section above */
}

/* Auto via OS preference */
@media (prefers-color-scheme: dark) {
  :root {
    --bg: #100e18;
    /* dark tokens */
  }
}
```

---

## Google Fonts

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?
  family=Cinzel:wght@400;500;600&
  family=Crimson+Pro:ital,wght@0,300;0,400;1,300;1,400&
  family=DM+Mono:wght@400;500&
  family=Noto+Serif+JP:wght@300;400
  &display=swap" rel="stylesheet">
```

---

*Konoha Forum Design System — Crystal Edition*
*木ノ葉隠れの里 · Version 2.0*
