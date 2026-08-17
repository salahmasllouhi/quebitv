# Design System Migration Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace all existing CSS color/font/spacing variables with the canonical Quebec IPTV design system tokens from `redesign/quebeciptv-design-system.md`.

**Architecture:** Consolidate two competing variable systems (`variables.css` legacy tokens and `redesign-theme.css` ad-hoc tokens) into one single source of truth in `variables.css`. Then do a full search-and-replace pass on every CSS file to swap old variable names for the new canonical ones. Primary accent changes from Teal (`#00D4AA`) to Indigo (`#5B4FE8`); teal is demoted to its new decorative role (checkmarks, badge borders).

**Tech Stack:** Pure CSS — no build tools. Files edited directly.

---

## Variable Mapping Reference

Use this table throughout all tasks. Old name → New canonical name:

| Old variable | New canonical variable |
|---|---|
| `--white` | `#ffffff` (hardcode inline) |
| `--bg` | `--bg-card` |
| `--bg-alt` | `--bg-section` |
| `--bg-primary` | `--bg-page` |
| `--bg-secondary` | `--bg-section` |
| `--dark` | `--color-navy` |
| `--dark-alt` | `--color-navy` |
| `--text` | `--text-primary` |
| `--text-secondary` | `--text-secondary` *(same name, new value)* |
| `--text-muted` | `--text-muted` *(same name, new value)* |
| `--border` | `--border-subtle` |
| `--border-medium` | `--border-subtle` |
| `--violet-500` | `--color-teal` *(teal, decorative only)* |
| `--violet-600` | `--color-indigo` |
| `--violet-700` | `--color-navy` |
| `--blue-50` | `--bg-section` |
| `--blue-100` | `--bg-section` |
| `--blue-500` | `--color-indigo` |
| `--blue-600` | `--color-indigo` |
| `--blue-700` | `--color-indigo-hover` |
| `--primary-deep` | `--color-navy` |
| `--primary-mid` | `--color-navy` |
| `--primary-light` | `--color-indigo` |
| `--accent-primary` | `--color-indigo` |
| `--accent-glow` | `--color-indigo` |
| `--accent-warm` | `--color-coral` |
| `--accent-gold` | `--color-gold` |
| `--shadow-sm` | `0 2px 8px rgba(15,40,71,0.06)` (hardcode) |
| `--shadow-md` | `0 8px 32px rgba(15,40,71,0.08)` (hardcode) |
| `--shadow-lg` | `0 16px 64px rgba(15,40,71,0.12)` (hardcode) |
| `--shadow-glow` | `0 8px 40px rgba(91,79,232,0.20)` (hardcode) |
| `--space-xs` | `--space-2` *(8px)* |
| `--space-sm` | `--space-4` *(16px)* |
| `--space-md` | `--space-6` *(24px)* |
| `--space-lg` | `--space-8` *(32px)* |
| `--space-xl` | `--space-10` *(40px)* |
| `--space-2xl` | `--space-12` *(48px)* |
| `--radius-sm` | `--radius-sm` *(same name, 8px — unchanged)* |
| `--radius-md` | `--radius-md` *(same name, 12px — unchanged)* |
| `--radius-lg` | `--radius-lg` *(now 16px, was 20px)* |
| `--radius-xl` | `--radius-lg` *(map to 16px)* |
| `--radius-full` | `--radius-pill` |

---

## Task 1: Replace `variables.css` — the single source of truth

**Files:**
- Modify: `front-page/css/variables.css`

- [ ] **Step 1: Overwrite variables.css with canonical design system tokens**

Replace the entire contents of `front-page/css/variables.css` with:

```css
/* Quebec IPTV Design System — canonical tokens */
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap');

:root {
  /* Brand colors */
  --color-navy:         #0F2847;
  --color-indigo:       #5B4FE8;
  --color-indigo-hover: #4A3FD4;
  --color-teal:         #00D4AA;
  --color-coral:        #FF6B4A;
  --color-gold:         #FFB830;
  --color-live:         #FF4757;

  /* Backgrounds */
  --bg-page:    #F5F5FF;
  --bg-section: #EBEBFF;
  --bg-card:    #FFFFFF;
  --bg-nav:     #FFFFFF;
  --bg-dark:    #0F2847;
  --bg-footer:  #091E38;

  /* Text */
  --text-primary:   #0F2847;
  --text-secondary: #4A6282;
  --text-muted:     #8B9DB8;
  --text-inverse:   #FFFFFF;
  --text-nav:       #4A4470;

  /* Borders */
  --border-subtle: rgba(15, 40, 71, 0.07);
  --border-indigo: rgba(91, 79, 232, 0.15);
  --border-teal:   rgba(0, 212, 170, 0.35);

  /* Spacing */
  --space-1:   4px;
  --space-2:   8px;
  --space-3:  12px;
  --space-4:  16px;
  --space-6:  24px;
  --space-8:  32px;
  --space-10: 40px;
  --space-12: 48px;

  /* Radius */
  --radius-xs:   6px;
  --radius-sm:   8px;
  --radius-md:  12px;
  --radius-lg:  16px;
  --radius-pill: 100px;

  /* Typography */
  --font: 'DM Sans', -apple-system, sans-serif;
}
```

- [ ] **Step 2: Commit**

```bash
git add front-page/css/variables.css
git commit -m "design: replace variables.css with canonical design system tokens"
```

---

## Task 2: Migrate `base.css`

**Files:**
- Modify: `front-page/css/base.css`

- [ ] **Step 1: Update body, buttons, and utility classes**

Replace the entire file contents with:

```css
/* Base Styles - Global reset and containers */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: var(--font);
    font-size: 16px;
    font-weight: 400;
    background: var(--bg-page);
    color: var(--text-secondary);
    line-height: 1.38;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 0.75rem;
}

section {
    padding: var(--space-12) 0;
}

/* Utility Classes */
.text-center {
    text-align: center;
}

.text-highlight {
    color: var(--color-indigo);
}

.text-gradient {
    background: linear-gradient(135deg, var(--color-indigo), var(--color-indigo-hover));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Common Button Styles */
.btn {
    display: inline-block;
    padding: 13px 30px;
    border-radius: var(--radius-pill);
    font-family: var(--font);
    font-size: 15px;
    font-weight: 600;
    line-height: 1.40;
    text-decoration: none;
    transition: all 0.2s;
    cursor: pointer;
    border: none;
}

.btn-primary {
    background: var(--color-indigo);
    color: #ffffff !important;
}

.btn-primary:hover {
    background: var(--color-indigo-hover);
    transform: translateY(-2px);
}

/* CTA Button */
.cta-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 13px 30px;
    background: var(--color-indigo);
    color: #ffffff;
    border: none;
    border-radius: var(--radius-pill);
    font-family: var(--font);
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    box-shadow: 0 8px 40px rgba(91, 79, 232, 0.20);
}

.cta-btn:hover {
    background: var(--color-indigo-hover);
    transform: translateY(-2px);
    box-shadow: 0 12px 40px rgba(91, 79, 232, 0.30);
}

/* Badge Styles */
.badge {
    display: inline-block;
    padding: 7px 15px;
    border-radius: var(--radius-pill);
    font-family: var(--font);
    font-size: 12px;
    font-weight: 600;
    position: absolute;
    top: -0.5rem;
    left: 50%;
    transform: translateX(-50%);
    white-space: nowrap;
}

.badge-green {
    background: rgba(0, 212, 170, 0.08);
    color: #0a9e82;
    border: 1.5px solid var(--border-teal);
}

.badge-orange {
    background: rgba(255, 107, 74, 0.10);
    color: var(--color-coral);
}
```

- [ ] **Step 2: Commit**

```bash
git add front-page/css/base.css
git commit -m "design: migrate base.css to canonical design system tokens"
```

---

## Task 3: Migrate `hero.css`

**Files:**
- Modify: `front-page/css/hero.css`

- [ ] **Step 1: Replace all old variable references in hero.css**

Apply these targeted replacements (use Edit tool, one at a time):

1. `color: var(--dark);` → `color: var(--color-navy);`  (hero h1)
2. `background: linear-gradient(135deg, var(--blue-600), #6366f1);` → `color: var(--color-indigo);`  (hero h1 span — change from gradient to flat indigo)
3. Remove `-webkit-background-clip: text;`, `-webkit-text-fill-color: transparent;`, `background-clip: text;` from hero h1 span
4. `color: var(--text-secondary);` → keep as-is (already correct name)
5. `background: rgba(124, 58, 237, 0.1);` → `background: rgba(91, 79, 232, 0.10);` (hero-badge bg)
6. `color: var(--violet-500);` → `color: var(--color-indigo);` (hero-badge color)
7. `background: var(--blue-600);` → `background: var(--color-indigo);` (hero-btn)
8. `color: var(--white);` → `color: #ffffff;` (hero-btn)
9. `box-shadow: 0 4px 15px rgba(109, 40, 217, 0.3);` → `box-shadow: 0 8px 40px rgba(91, 79, 232, 0.20);`
10. `background: var(--blue-700);` → `background: var(--color-indigo-hover);` (hero-btn hover)
11. `box-shadow: 0 6px 20px rgba(109, 40, 217, 0.4);` → `box-shadow: 0 12px 40px rgba(91, 79, 232, 0.30);`
12. `color: var(--dark);` → `color: var(--color-navy);` (hero-stat-num)
13. `color: var(--text-secondary);` → keep as-is (hero-stat-label, already correct)

- [ ] **Step 2: Commit**

```bash
git add front-page/css/hero.css
git commit -m "design: migrate hero.css to canonical design system tokens"
```

---

## Task 4: Migrate `pricing.css`

**Files:**
- Modify: `front-page/css/pricing.css`

- [ ] **Step 1: Replace all old variable references in pricing.css**

Apply these replacements:

1. `background: var(--bg-secondary);` → `background: var(--bg-section);`  (`.pricing`)
2. `color: var(--primary-deep);` → `color: var(--color-navy);`  (`.pricing-header h2`, `.config-title`)
3. `color: var(--accent-primary);` → `color: var(--color-indigo);`  (`.pricing-header h2 span`, `.duration-savings`, `.config-title svg`)
4. `background: var(--bg-card);` → keep as-is (`.configurator`, `.select-card`)
5. `box-shadow: var(--shadow-lg);` → `box-shadow: 0 16px 64px rgba(15, 40, 71, 0.12);`
6. `background: var(--bg-secondary);` → `background: var(--bg-section);`  (`.config-section`, `.trust-badges > div`)
7. `border: 2px solid var(--border-medium);` → `border: 2px solid var(--border-subtle);`
8. `border-color: var(--accent-primary);` → `border-color: var(--color-indigo);`  (`.select-card:hover`, `.select-card.active`)
9. `background: rgba(0, 212, 170, 0.1);` → `background: rgba(91, 79, 232, 0.08);`  (`.select-card.active`)
10. `color: var(--text-primary);` → keep as-is
11. `color: var(--text-secondary);` → keep as-is
12. `color: var(--accent-warm);` → `color: var(--color-coral);`  (`.duration-price`)
13. `color: var(--text-muted);` → keep as-is

- [ ] **Step 2: Commit**

```bash
git add front-page/css/pricing.css
git commit -m "design: migrate pricing.css to canonical design system tokens"
```

---

## Task 5: Migrate `footer.css`

**Files:**
- Modify: `front-page/css/footer.css`

- [ ] **Step 1: Replace old variable references in footer.css**

Apply these replacements:

1. `background: var(--primary-deep);` → `background: var(--bg-footer);`  (`.site-footer`)
2. `color: var(--text-inverse);` → keep as-is
3. `color: var(--accent-primary);` → `color: var(--color-teal);`  (`.footer-brand span` — teal is correct in footer as decorative accent per design system)
4. `color: var(--white);` → `color: #ffffff;`  (`.footer-col h4`)
5. `background: rgba(124, 58, 237, 0.2);` → `background: rgba(91, 79, 232, 0.15);`  (`.footer-country-option:hover`)

- [ ] **Step 2: Commit**

```bash
git add front-page/css/footer.css
git commit -m "design: migrate footer.css to canonical design system tokens"
```

---

## Task 6: Migrate `redesign-theme.css` — the largest file

**Files:**
- Modify: `front-page/css/redesign-theme.css`

- [ ] **Step 1: Remove the old @import font line**

Find and remove:
```css
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap');
```

- [ ] **Step 2: Replace the :root block**

Find the entire `:root { ... }` block at the top of `redesign-theme.css` (lines 12–57) and remove it entirely. `variables.css` is now the single source of truth.

- [ ] **Step 3: Replace variable references — spacing**

Do a global replace across the file:
- `var(--space-xs)` → `var(--space-2)`
- `var(--space-sm)` → `var(--space-4)`
- `var(--space-md)` → `var(--space-6)`
- `var(--space-lg)` → `var(--space-8)`
- `var(--space-xl)` → `var(--space-10)`
- `var(--space-2xl)` → `var(--space-12)`

- [ ] **Step 4: Replace variable references — radius**

- `var(--radius-xl)` → `var(--radius-lg)`
- `var(--radius-full)` → `var(--radius-pill)`

- [ ] **Step 5: Replace variable references — colors**

- `var(--accent-primary)` → `var(--color-indigo)`
- `var(--accent-glow)` → `var(--color-indigo)`
- `var(--accent-warm)` → `var(--color-coral)`
- `var(--accent-gold)` → `var(--color-gold)`
- `var(--primary-deep)` → `var(--color-navy)`
- `var(--primary-mid)` → `var(--color-navy)`
- `var(--primary-light)` → `var(--color-indigo)`
- `var(--bg-primary)` → `var(--bg-page)`
- `var(--bg-secondary)` → `var(--bg-section)`
- `var(--border-medium)` → `var(--border-subtle)`

- [ ] **Step 6: Replace hardcoded shadow values**

- `var(--shadow-sm)` → `0 2px 8px rgba(15, 40, 71, 0.06)`
- `var(--shadow-md)` → `0 8px 32px rgba(15, 40, 71, 0.08)`
- `var(--shadow-lg)` → `0 16px 64px rgba(15, 40, 71, 0.12)`
- `var(--shadow-glow)` → `0 8px 40px rgba(91, 79, 232, 0.20)`

- [ ] **Step 7: Replace old teal rgba and violet rgba hardcoded values**

Search for and replace:
- `rgba(0, 212, 170,` → `rgba(91, 79, 232,`  (anywhere teal was used as a primary/interactive color — backgrounds on hover states, active states, glows)
- `rgba(124, 58, 237,` → `rgba(91, 79, 232,`
- `rgba(109, 40, 217,` → `rgba(91, 79, 232,`

- [ ] **Step 8: Replace font references**

- `font-family: 'Outfit', sans-serif;` → `font-family: var(--font);`
- `font-family: 'Space Mono', monospace;` → `font-family: var(--font);`
- `font-family: 'DM Sans', sans-serif;` → `font-family: var(--font);`
- `font-family: 'Bricolage Grotesque', sans-serif;` → `font-family: var(--font);`

- [ ] **Step 9: Commit**

```bash
git add front-page/css/redesign-theme.css
git commit -m "design: migrate redesign-theme.css to canonical design system tokens"
```

---

## Task 7: Migrate remaining CSS files

**Files:**
- Modify: `front-page/css/responsive.css`
- Modify: `front-page/css/features.css` (if it contains old tokens)
- Modify: `front-page/css/sports.css` (if it contains old tokens)

- [ ] **Step 1: Check and fix responsive.css**

The file has no variable references to old tokens, but confirm by running:
```bash
grep -n "var(--violet\|var(--blue-\|var(--primary-\|var(--accent-\|var(--bg-primary\|var(--bg-secondary\|var(--white\|var(--dark\b\|Outfit\|Bricolage\|Space Mono" front-page/css/responsive.css
```
If any matches: apply the same mapping table from the reference above.

- [ ] **Step 2: Check and fix features.css and sports.css**

```bash
grep -n "var(--violet\|var(--blue-\|var(--primary-\|var(--accent-\|var(--bg-primary\|var(--bg-secondary\|var(--white\b\|var(--dark\b\|Outfit\|Bricolage\|Space Mono" \
  front-page/css/features.css front-page/css/sports.css
```
Apply the same mapping table for any matches found.

- [ ] **Step 3: Full sweep — catch any remaining old tokens across ALL CSS files**

```bash
grep -rn "var(--violet\|var(--blue-\|var(--primary-\|var(--accent-\|var(--bg-primary\|var(--bg-secondary\|var(--bg-alt\|var(--white\b\|var(--dark\b\|var(--dark-alt\|var(--text\b\|var(--border\b\|var(--shadow-\|var(--space-xs\|var(--space-sm\|var(--space-md\|var(--space-lg\|var(--space-xl\|var(--space-2xl\|var(--radius-xl\|var(--radius-full\|Outfit\|Bricolage\|Space Mono" \
  front-page/css/
```
Fix any remaining occurrences using the variable mapping table.

- [ ] **Step 4: Commit**

```bash
git add front-page/css/
git commit -m "design: sweep remaining CSS files for old design tokens"
```

---

## Task 8: Update `redesign-theme.css` body and typography declarations

**Files:**
- Modify: `front-page/css/redesign-theme.css`

- [ ] **Step 1: Update body block to match design system spec**

Find the `body { ... }` block in redesign-theme.css and update to:

```css
body {
    font-family: var(--font);
    font-size: 16px;
    font-weight: 400;
    background: var(--bg-page);
    color: var(--text-secondary);
    line-height: 1.38;
}
```

- [ ] **Step 2: Update h2 and h3 to match design system scale**

Find any `h2` declarations and ensure:
```css
h2 {
    font-size: 36px;
    font-weight: 700;
    line-height: 1.22;
    letter-spacing: -0.5px;
    color: var(--color-navy);
}
```

Find any `h3` declarations and ensure:
```css
h3 {
    font-size: 22px;
    font-weight: 600;
    line-height: 1.20;
    color: var(--color-navy);
}
```

- [ ] **Step 3: Commit**

```bash
git add front-page/css/redesign-theme.css
git commit -m "design: align body and heading typography to design system scale"
```

---

## Task 9: Verify — final token audit

- [ ] **Step 1: Run a final sweep for any leaked old tokens**

```bash
grep -rn \
  "var(--violet\|var(--blue-\|var(--primary-\|var(--accent-\|var(--bg-primary\|var(--bg-secondary\|var(--bg-alt\|var(--white\b\|'Outfit'\|'Space Mono'\|'Bricolage\|var(--shadow-\|var(--space-xs\|var(--space-sm\b\|var(--space-md\|var(--space-lg\b\|var(--space-xl\b\|var(--space-2xl\|var(--radius-full\|var(--radius-xl" \
  front-page/css/
```

Expected: **0 matches.** If any remain, fix and commit.

- [ ] **Step 2: Confirm DM Sans is the only imported font**

```bash
grep -rn "@import\|font-family" front-page/css/
```

Expected: only `DM Sans` references remain. No `Outfit`, `Space Mono`, or `Bricolage Grotesque`.

- [ ] **Step 3: Final commit**

```bash
git add -A
git commit -m "design: complete Quebec IPTV design system migration"
```
