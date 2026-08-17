# Quebec IPTV — Design System

---

## Colors

### Palette

| Name | Hex | Variable | Usage |
|------|-----|----------|-------|
| Deep Navy | `#0F2847` | `--color-navy` | Headlines (lines 1 & 3), body text, dark sections, footer |
| Indigo | `#5B4FE8` | `--color-indigo` | Hero accent headline (line 2), CTAs, stat numbers, active states |
| Indigo Hover | `#4A3FD4` | `--color-indigo-hover` | Button hover state |
| Teal | `#00D4AA` | `--color-teal` | Checkmarks, badge borders, savings badge, footer trust icons, pricing value |
| Coral | `#FF6B4A` | `--color-coral` | Competitor pricing, urgency only |
| Gold | `#FFB830` | `--color-gold` | Star ratings only |
| Live Red | `#FF4757` | `--color-live` | Live indicator dot only |

> **Teal rule:** never used as a text color. Only as checkmark, border accent, or badge fill.

### Backgrounds

| Name | Hex | Variable | Usage |
|------|-----|----------|-------|
| Page | `#F5F5FF` | `--bg-page` | Main page background |
| Section Alt | `#EBEBFF` | `--bg-section` | Features, steps sections |
| Card | `#FFFFFF` | `--bg-card` | All card surfaces |
| Nav | `#FFFFFF` | `--bg-nav` | Navigation |
| Dark | `#0F2847` | `--bg-dark` | Footer CTA, logo strip |
| Footer | `#091E38` | `--bg-footer` | Footer bar |

### Text

| Name | Hex | Variable | Usage |
|------|-----|----------|-------|
| Primary | `#0F2847` | `--text-primary` | Main headlines |
| Secondary | `#4A6282` | `--text-secondary` | Body paragraphs, descriptions |
| Muted | `#8B9DB8` | `--text-muted` | Captions, timestamps |
| Inverse | `#FFFFFF` | `--text-inverse` | Text on dark backgrounds |
| Nav | `#4A4470` | `--text-nav` | Navigation links |

### Borders

| Variable | Value | Usage |
|----------|-------|-------|
| `--border-subtle` | `rgba(15, 40, 71, 0.07)` | Default card borders |
| `--border-indigo` | `rgba(91, 79, 232, 0.15)` | Indigo-accented cards |
| `--border-teal` | `rgba(0, 212, 170, 0.35)` | Teal badge borders |

### CSS Variables

```css
:root {
  --color-navy:         #0F2847;
  --color-indigo:       #5B4FE8;
  --color-indigo-hover: #4A3FD4;
  --color-teal:         #00D4AA;
  --color-coral:        #FF6B4A;
  --color-gold:         #FFB830;
  --color-live:         #FF4757;

  --bg-page:            #F5F5FF;
  --bg-section:         #EBEBFF;
  --bg-card:            #FFFFFF;
  --bg-nav:             #FFFFFF;
  --bg-dark:            #0F2847;
  --bg-footer:          #091E38;

  --text-primary:       #0F2847;
  --text-secondary:     #4A6282;
  --text-muted:         #8B9DB8;
  --text-inverse:       #FFFFFF;
  --text-nav:           #4A4470;

  --border-subtle:      rgba(15, 40, 71, 0.07);
  --border-indigo:      rgba(91, 79, 232, 0.15);
  --border-teal:        rgba(0, 212, 170, 0.35);
}
```

---

## Typography

**Single font:** DM Sans — all roles, all sizes.

### Import

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
```

### Scale

| Role | Size | Weight | Line Height | Letter Spacing |
|------|------|--------|-------------|----------------|
| Display Hero | 48px | 700 | 1.17 | -1px |
| Section Heading | 36px | 700 | 1.22 | -0.5px |
| Feature Title | 22px | 600 | 1.20 | — |
| Stat Number | 32px | 700 | 1.00 | -0.5px |
| Body | 16px | 400 | 1.38 | — |
| Body Emphasis | 16px | 500 | 1.38 | — |
| Button | 15px | 600 | 1.40 | — |
| Caption | 14px | 600 | 1.71 | — |
| Badge | 12px | 600 | 1.00 | — |
| Section Label | 11px | 600 | 1.00 | 0.06em · UPPERCASE |

### CSS

```css
:root {
  --font: 'DM Sans', -apple-system, sans-serif;
}

body {
  font-family: var(--font);
  font-size: 16px;
  font-weight: 400;
  line-height: 1.38;
  color: var(--text-secondary);
  background: var(--bg-page);
}

.hero-headline {
  font-size: 48px;
  font-weight: 700;
  line-height: 1.17;
  letter-spacing: -1px;
  color: var(--color-navy);
}
.hero-headline--accent { color: var(--color-indigo); }

h2 {
  font-size: 36px;
  font-weight: 700;
  line-height: 1.22;
  letter-spacing: -0.5px;
  color: var(--color-navy);
}

h3 {
  font-size: 22px;
  font-weight: 600;
  line-height: 1.20;
  color: var(--color-navy);
}

.stat-number {
  font-size: 32px;
  font-weight: 700;
  line-height: 1.0;
  letter-spacing: -0.5px;
}

.btn {
  font-size: 15px;
  font-weight: 600;
  line-height: 1.40;
  border-radius: 100px;
  padding: 13px 30px;
  border: none;
  cursor: pointer;
}
.btn--primary { background: var(--color-indigo); color: #fff; }
.btn--primary:hover { background: var(--color-indigo-hover); }
.btn--dark    { background: var(--color-navy);   color: #fff; }

.caption {
  font-size: 14px;
  font-weight: 600;
  line-height: 1.71;
  color: var(--text-muted);
}

.badge {
  font-size: 12px;
  font-weight: 600;
  border-radius: 100px;
  padding: 7px 15px;
}
.badge--dark { background: var(--color-navy); color: #fff; }
.badge--teal { background: rgba(0,212,170,0.08); color: #0a9e82; border: 1.5px solid var(--border-teal); }

.section-label {
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: var(--text-muted);
}
```

---

## Spacing

| Token | Variable | Value | Usage |
|-------|----------|-------|-------|
| ▏ | `--space-1` | `4px` | Icon gaps, tight inline spacing |
| ▎ | `--space-2` | `8px` | Component internal gaps |
| ▍ | `--space-3` | `12px` | Card inner padding (compact) |
| ▌ | `--space-4` | `16px` | Card padding, stack gaps |
| █ | `--space-6` | `24px` | Section inner spacing |
| | `--space-8` | `32px` | Section padding horizontal |
| | `--space-10` | `40px` | Section padding vertical |
| | `--space-12` | `48px` | Hero padding, large section gaps |

```css
:root {
  --space-1:   4px;
  --space-2:   8px;
  --space-3:  12px;
  --space-4:  16px;
  --space-6:  24px;
  --space-8:  32px;
  --space-10: 40px;
  --space-12: 48px;
}
```

---

## Radius

| Token | Variable | Value | Usage |
|-------|----------|-------|-------|
| ▢ `6px` | `--radius-xs` | `6px` | Small tags, micro badges |
| ▢ `8px` | `--radius-sm` | `8px` | Icon containers, inputs |
| ▢ `12px` | `--radius-md` | `12px` | Cards (default) |
| ▢ `16px` | `--radius-lg` | `16px` | Large cards, modals |
| ● `pill` | `--radius-pill` | `100px` | Buttons, badges, nav CTA |

```css
:root {
  --radius-xs:   6px;
  --radius-sm:   8px;
  --radius-md:  12px;
  --radius-lg:  16px;
  --radius-pill: 100px;
}
```
