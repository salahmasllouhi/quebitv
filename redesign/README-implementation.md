# Quebec IPTV Redesign - Implementation Guide

## Files Overview

| File | Description |
|------|-------------|
| `00-css-variables-base.css` | **REQUIRED FIRST** - Color palette, variables, base styles |
| `01-section-hero.html` | Hero section with stats cards |
| `02-section-logos-bar.html` | Trust logos/brands bar |
| `03-section-features.html` | 6 feature cards grid |
| `04-section-sports.html` | Sports categories with LIVE indicators |
| `05-section-price-comparison.html` | "Without vs With" comparison + savings |
| `06-section-steps.html` | 3-step setup process |
| `07-section-devices.html` | Device compatibility icons |
| `08-section-faq.html` | FAQ accordion (includes JS) |
| `09-section-cta.html` | Final call-to-action section |
| `10-javascript.js` | Scroll animations & interactions |

---

## Implementation Steps

### Step 1: Add Google Fonts to your `<head>`

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
```

### Step 2: Add CSS Variables

Add the contents of `00-css-variables-base.css` to your WordPress theme's `style.css` or create a custom CSS file.

### Step 3: Add Sections in Order

Copy each section's HTML into your WordPress page (using a page builder, custom template, or raw HTML block):

1. **Your WordPress Header** (existing)
2. `01-section-hero.html`
3. `02-section-logos-bar.html`
4. `03-section-features.html`
5. `04-section-sports.html`
6. `05-section-price-comparison.html`
7. **Your Existing Pricing Section** (keep as-is)
8. `06-section-steps.html`
9. `07-section-devices.html`
10. **Your Existing Reviews Section** (keep as-is)
11. `08-section-faq.html`
12. `09-section-cta.html`
13. **Your WordPress Footer** (existing)

### Step 4: Add JavaScript

Add `10-javascript.js` before the closing `</body>` tag, or enqueue it in WordPress.

---

## Section Order on Page

```
┌─────────────────────────────────────┐
│         WordPress Header            │  ← Your existing
├─────────────────────────────────────┤
│         01 - Hero Section           │  ← NEW
├─────────────────────────────────────┤
│         02 - Logos Bar              │  ← NEW
├─────────────────────────────────────┤
│         03 - Features Grid          │  ← NEW
├─────────────────────────────────────┤
│         04 - Sports Section         │  ← NEW
├─────────────────────────────────────┤
│     05 - Price Comparison           │  ← NEW
├─────────────────────────────────────┤
│     YOUR EXISTING PRICING           │  ← Keep yours
├─────────────────────────────────────┤
│         06 - Setup Steps            │  ← NEW
├─────────────────────────────────────┤
│         07 - Devices                │  ← NEW
├─────────────────────────────────────┤
│     YOUR EXISTING REVIEWS           │  ← Keep yours
├─────────────────────────────────────┤
│         08 - FAQ                    │  ← NEW
├─────────────────────────────────────┤
│         09 - Final CTA              │  ← NEW
├─────────────────────────────────────┤
│         WordPress Footer            │  ← Your existing
└─────────────────────────────────────┘
```

---

## Color Palette Reference

| Color | Variable | Hex | Usage |
|-------|----------|-----|-------|
| Deep Navy | `--primary-deep` | #0F2847 | Headlines, footer bg |
| Mid Blue | `--primary-mid` | #1A4B7C | Gradients |
| Light Blue | `--primary-light` | #3D7AB8 | Links, gradient end |
| Teal | `--accent-primary` | #00D4AA | CTAs, highlights |
| Bright Teal | `--accent-glow` | #00F5C4 | Hover states |
| Coral | `--accent-warm` | #FF6B4A | Prices, urgency |
| Gold | `--accent-gold` | #FFB830 | Stars, ratings |
| Background | `--bg-primary` | #FAFCFF | Main bg |
| Alt Background | `--bg-secondary` | #F0F4FA | Section alternates |

---

## WordPress Integration Tips

### Option A: Using Elementor/Page Builder
1. Create a new page
2. Add HTML widget/block for each section
3. Paste the HTML (without `<style>` tags)
4. Add CSS to Customizer → Additional CSS

### Option B: Using Custom Page Template
1. Create `page-landing.php` in your theme
2. Copy all sections into the template
3. Assign template to your landing page

### Option C: Using Gutenberg
1. Add Custom HTML block for each section
2. Include both HTML and `<style>` tags
3. Add JavaScript via plugin or footer

---

## Customization

### Change Colors
Edit the `:root` variables in `00-css-variables-base.css`

### Change Content
Edit the text directly in each HTML section file

### Add/Remove FAQ Items
Copy/paste `.faq-item` blocks in `08-section-faq.html`

### Add/Remove Sports
Copy/paste `.sport-card` blocks in `04-section-sports.html`

---

## Need Help?

If your AI agent needs to make changes:
1. Reference this README for structure
2. Keep the CSS variables consistent
3. Maintain the class naming convention
4. Test responsive breakpoints after changes
