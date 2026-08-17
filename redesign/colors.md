# Quebec IPTV - Complete Color Palette & Design System

---

## PRIMARY BLUES (Trust & Professionalism)

| Name        | Variable           | Hex       | Usage                          |
|-------------|--------------------|-----------|---------------------------------|
| Deep Navy   | `--primary-deep`   | `#0F2847` | Headlines, footer background    |
| Mid Blue    | `--primary-mid`    | `#1A4B7C` | Gradients, secondary elements   |
| Light Blue  | `--primary-light`  | `#3D7AB8` | Links, gradient endpoints       |

---

## ACCENT COLORS (Energy & Action)

| Name         | Variable           | Hex       | Usage                              |
|--------------|--------------------|-----------|------------------------------------|
| Teal         | `--accent-primary` | `#00D4AA` | CTAs, buttons, highlights, checks  |
| Bright Teal  | `--accent-glow`    | `#00F5C4` | Hover states, glows                |
| Coral/Orange | `--accent-warm`    | `#FF6B4A` | Prices, urgency, warnings          |
| Gold         | `--accent-gold`    | `#FFB830` | Stars, ratings, premium badges     |
| Live Red     | (no variable)      | `#FF4757` | Live indicator dot                 |

---

## BACKGROUND COLORS (Light Mode)

| Name         | Variable         | Hex/Value                    | Usage                    |
|--------------|------------------|------------------------------|--------------------------|
| Primary BG   | `--bg-primary`   | `#FAFCFF`                    | Main page background     |
| Secondary BG | `--bg-secondary` | `#F0F4FA`                    | Alternate sections       |
| Card BG      | `--bg-card`      | `#FFFFFF`                    | Cards, elevated elements |
| Nav BG       | `--bg-nav`       | `rgba(250, 252, 255, 0.85)`  | Navigation (85% opacity) |
| Nav Scrolled | `--bg-nav-scrolled` | `rgba(250, 252, 255, 0.95)` | Nav when scrolling (95%) |

---

## TEXT COLORS

| Name           | Variable           | Hex       | Usage                      |
|----------------|--------------------|-----------|-----------------------------|
| Primary Text   | `--text-primary`   | `#0F2847` | Headlines, titles           |
| Secondary Text | `--text-secondary` | `#4A6282` | Body text, descriptions     |
| Muted Text     | `--text-muted`     | `#8B9DB8` | Captions, labels            |
| Inverse Text   | `--text-inverse`   | `#FFFFFF` | Text on dark backgrounds    |

---

## BORDERS (Semi-transparent)

| Name          | Variable          | Value                       | Usage                 |
|---------------|-------------------|-----------------------------|-----------------------|
| Subtle Border | `--border-subtle` | `rgba(15, 40, 71, 0.08)`    | Light borders, dividers |
| Medium Border | `--border-medium` | `rgba(15, 40, 71, 0.12)`    | Stronger borders      |

---

## SHADOWS

| Name         | Variable        | Value                                  | Usage                  |
|--------------|-----------------|----------------------------------------|------------------------|
| Small Shadow | `--shadow-sm`   | `0 2px 8px rgba(15, 40, 71, 0.06)`     | Subtle elevation       |
| Medium Shadow| `--shadow-md`   | `0 8px 32px rgba(15, 40, 71, 0.08)`    | Cards on hover         |
| Large Shadow | `--shadow-lg`   | `0 16px 64px rgba(15, 40, 71, 0.12)`   | Modals, dropdowns      |
| Glow Shadow  | `--shadow-glow` | `0 8px 40px rgba(0, 212, 170, 0.25)`   | CTA buttons (teal glow)|

---

## TRANSPARENCY & BLUR EFFECTS

### Navigation Bar
```css
.nav {
    background: rgba(250, 252, 255, 0.85);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(15, 40, 71, 0.08);
}

.nav.scrolled {
    background: rgba(250, 252, 255, 0.95);
    box-shadow: 0 2px 8px rgba(15, 40, 71, 0.06);
}
```

### Other Transparent Effects
| Element                | Value                         | Usage                    |
|------------------------|-------------------------------|--------------------------|
| Hero Rating Badge BG   | `rgba(15, 40, 71, 0.6)`       | Dark semi-transparent pill |
| Savings Badge BG       | `rgba(0, 212, 170, 0.1)`      | Light teal tint          |
| Savings Badge Border   | `rgba(0, 212, 170, 0.3)`      | Teal border              |
| Feature Card Hover BG  | `rgba(0, 212, 170, 0.05)`     | Very light teal on hover |
| Accent Primary 10%     | `rgba(0, 212, 170, 0.1)`      | Tags, badges background  |
| Accent Primary 12%     | `rgba(0, 212, 170, 0.12)`     | Feature icon background  |
| Primary Light 8%       | `rgba(26, 75, 124, 0.08)`     | Gradient backgrounds     |
| Live Dot Glow          | `rgba(255, 71, 87, 0.7)`      | Red pulse animation      |
| Card Hover Border      | `rgba(0, 212, 170, 0.3)`      | Teal border on hover     |

---

## COMPLETE CSS VARIABLES

Copy and paste this into your CSS file:

```css
:root {
    /* ==========================================
       PRIMARY BLUES - Trust & Professionalism
       ========================================== */
    --primary-deep: #0F2847;
    --primary-mid: #1A4B7C;
    --primary-light: #3D7AB8;
    
    /* ==========================================
       ACCENT COLORS - Energy & Action
       ========================================== */
    --accent-primary: #00D4AA;
    --accent-glow: #00F5C4;
    --accent-warm: #FF6B4A;
    --accent-gold: #FFB830;
    
    /* ==========================================
       BACKGROUND COLORS - Light Mode
       ========================================== */
    --bg-primary: #FAFCFF;
    --bg-secondary: #F0F4FA;
    --bg-card: #FFFFFF;
    --bg-nav: rgba(250, 252, 255, 0.85);
    --bg-nav-scrolled: rgba(250, 252, 255, 0.95);
    
    /* ==========================================
       TEXT COLORS
       ========================================== */
    --text-primary: #0F2847;
    --text-secondary: #4A6282;
    --text-muted: #8B9DB8;
    --text-inverse: #FFFFFF;
    
    /* ==========================================
       BORDERS (Semi-transparent)
       ========================================== */
    --border-subtle: rgba(15, 40, 71, 0.08);
    --border-medium: rgba(15, 40, 71, 0.12);
    
    /* ==========================================
       SHADOWS
       ========================================== */
    --shadow-sm: 0 2px 8px rgba(15, 40, 71, 0.06);
    --shadow-md: 0 8px 32px rgba(15, 40, 71, 0.08);
    --shadow-lg: 0 16px 64px rgba(15, 40, 71, 0.12);
    --shadow-glow: 0 8px 40px rgba(0, 212, 170, 0.25);
    
    /* ==========================================
       SPACING
       ========================================== */
    --space-xs: 0.5rem;
    --space-sm: 1rem;
    --space-md: 1.5rem;
    --space-lg: 2.5rem;
    --space-xl: 4rem;
    --space-2xl: 6rem;
    
    /* ==========================================
       BORDER RADIUS
       ========================================== */
    --radius-sm: 8px;
    --radius-md: 12px;
    --radius-lg: 20px;
    --radius-xl: 28px;
    --radius-full: 100px;
}
```

---

## GRADIENT EXAMPLES

### Primary CTA Button Gradient
```css
background: linear-gradient(135deg, #00D4AA, #3D7AB8);
/* or using variables: */
background: linear-gradient(135deg, var(--accent-primary), var(--primary-light));
```

### Gradient Text
```css
.gradient-text {
    background: linear-gradient(135deg, #00D4AA, #3D7AB8);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
```

### Dark Section Gradient (CTA/Footer)
```css
background: linear-gradient(135deg, #0F2847, #1A4B7C);
/* or using variables: */
background: linear-gradient(135deg, var(--primary-deep), var(--primary-mid));
```

### Aurora Background Effect
```css
.bg-aurora::before {
    background: 
        radial-gradient(ellipse 80% 50% at 20% 10%, rgba(0, 212, 170, 0.06) 0%, transparent 50%),
        radial-gradient(ellipse 60% 40% at 80% 20%, rgba(26, 75, 124, 0.05) 0%, transparent 50%),
        radial-gradient(ellipse 50% 30% at 50% 80%, rgba(255, 107, 74, 0.03) 0%, transparent 50%);
}
```

### Feature Icon Background Gradient
```css
background: linear-gradient(135deg, rgba(0, 212, 170, 0.12), rgba(26, 75, 124, 0.08));
```

---

## BUTTON STYLES

### Primary Button (Teal Gradient with Glow)
```css
.btn-primary {
    background: linear-gradient(135deg, #00D4AA, #3D7AB8);
    color: #FFFFFF;
    box-shadow: 0 8px 40px rgba(0, 212, 170, 0.25);
    border: none;
    padding: 14px 28px;
    border-radius: 100px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 48px rgba(0, 212, 170, 0.4);
}
```

### Secondary Button (Outlined)
```css
.btn-secondary {
    background: #FFFFFF;
    color: #0F2847;
    border: 2px solid rgba(15, 40, 71, 0.12);
    padding: 14px 28px;
    border-radius: 100px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-secondary:hover {
    border-color: #00D4AA;
    background: rgba(0, 212, 170, 0.05);
}
```

---

## CARD STYLES

### Default Card
```css
.card {
    background: #FFFFFF;
    border: 1px solid rgba(15, 40, 71, 0.08);
    border-radius: 20px;
    padding: 2.5rem;
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-6px);
    box-shadow: 0 8px 32px rgba(15, 40, 71, 0.08);
    border-color: rgba(0, 212, 170, 0.3);
}
```

### Card with Top Accent Line on Hover
```css
.card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #00D4AA, #3D7AB8);
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.4s ease;
}

.card:hover::before {
    transform: scaleX(1);
}
```

---

## LIVE INDICATOR ANIMATION

```css
.live-dot {
    width: 10px;
    height: 10px;
    background: #FF4757;
    border-radius: 50%;
    animation: livePulse 1.5s ease-in-out infinite;
}

@keyframes livePulse {
    0%, 100% { 
        opacity: 1;
        box-shadow: 0 0 0 0 rgba(255, 71, 87, 0.7);
    }
    50% { 
        opacity: 0.8;
        box-shadow: 0 0 0 8px rgba(255, 71, 87, 0);
    }
}
```

---

## VISUAL COLOR REFERENCE

```
PRIMARY BLUES:
████████  #0F2847  Deep Navy (Headlines, Footer)
████████  #1A4B7C  Mid Blue (Gradients)
████████  #3D7AB8  Light Blue (Links, Gradient end)

ACCENTS:
████████  #00D4AA  Teal (Main CTA, Highlights)
████████  #00F5C4  Bright Teal (Hover states)
████████  #FF6B4A  Coral (Prices, Urgency)
████████  #FFB830  Gold (Stars, Ratings)
████████  #FF4757  Red (Live indicator)

BACKGROUNDS:
████████  #FAFCFF  Primary Background
████████  #F0F4FA  Secondary Background
████████  #FFFFFF  Card Background

TEXT:
████████  #0F2847  Primary Text
████████  #4A6282  Secondary Text
████████  #8B9DB8  Muted Text
████████  #FFFFFF  Inverse Text (on dark)
```

---

## TYPOGRAPHY

### Font Family
```css
/* Primary Font - Headlines & UI */
font-family: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif;

/* Secondary Font - Numbers & Monospace */
font-family: 'Space Mono', monospace;
```

### Google Fonts Import
```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
```

---

## SUMMARY

This color palette creates a **clean, professional, light-mode design** with:

- **Navy blues** for trust and authority
- **Teal accents** for energy and CTAs
- **Coral/orange** for urgency and pricing
- **Subtle transparencies** for modern glass effects
- **Soft shadows** for depth without heaviness

The palette is designed to:
1. Build trust (professional blues)
2. Drive action (vibrant teal CTAs)
3. Create urgency (coral pricing)
4. Feel modern (blur effects, subtle gradients)
5. Stay light and clean (white/off-white backgrounds)