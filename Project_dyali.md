# Project Dyali – Memory File

> **Last Updated:** 2026-01-25

---

## Overview

**My IPTV Theme** — A modern WordPress theme for selling IPTV streaming subscriptions with WooCommerce integration and multisite support.

**Primary Domain:** quebeciptv.co

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| CMS | WordPress Multisite |
| E-commerce | WooCommerce |
| Frontend | PHP + Vanilla JS + CSS |
| Fonts | Inter (Google Fonts) |

---

## Architecture

```
my-iptv-theme/
├── front-page/
│   ├── css/           # Modular CSS (16 files)
│   ├── js/            # JavaScript (5 files)
│   ├── sections/      # Landing page sections
│   │   ├── hero.php, header.php, footer.php
│   │   ├── pricing.php, features.php, brands.php
│   │   ├── comparison.php, reviews.php, sports.php
│   │   ├── steps.php, contact.php, dark-cta.php, unlock.php
│   └── partials/
│       └── checkout/  # Checkout partials (thank-you.php)
├── inc/               # PHP includes
│   ├── geo-redirect.php       # Geo-based redirection
│   ├── network-cloner.php     # Multisite cloning
│   ├── currency-settings.php  # Multi-currency pricing
│   ├── content-settings.php   # Front page content settings
│   ├── admin-bulk-editor.php  # Bulk product management
│   ├── product-setup.php      # WooCommerce product setup
│   ├── user-guide-shortcode.php
│   ├── universal-header.php
│   └── seo-manager.php        # Disabled (using Rank Math Pro)
├── template-store-checkout.php
├── template-store-shop.php
├── template-store-cart.php
├── front-page.php
├── functions.php
└── style.css
```

---

## Environment & Deployment

### Constants
- `IPTV_MAIN_SITE_URL` — Main site URL for cross-site cart (quebeciptv.co)

### Environment Variables (names only)
- OpenAI API key (stored in WordPress options)

---

## Features Implemented

### Landing Page Sections
- [x] Hero section
- [x] Features showcase
- [x] Pricing table
- [x] Brand logos
- [x] Comparison table
- [x] Customer reviews
- [x] Sports section
- [x] Steps/How-it-works
- [x] Contact form
- [x] Dark CTA section

### WooCommerce Customizations
- [x] Simplified checkout (email + phone only required)
- [x] Cart disabled — redirect to checkout
- [x] Shop/category pages redirect to home
- [x] "Buy Now" instead of "Add to Cart"
- [x] Cross-site cart (subsites redirect to main site checkout)
- [x] Hide trial products from related products

### Multisite Features
- [x] Geo-redirect system
- [x] Network cloner utility
- [x] Multi-currency pricing

### Admin Features
- [x] Bulk product editor
- [x] Content settings with OpenAI translation
- [x] User guide shortcode

---

## Bugs & Fixes

| Date | Bug | Resolution |
|------|-----|------------|
| — | — | — |

---

## Decisions Log

| Date | Decision | Rationale |
|------|----------|-----------|
| — | SEO Manager disabled | Using Rank Math Pro instead |
| — | Cart page disabled | Direct checkout flow |
| 2026-01-25 | Non-English/Swedish languages disabled | Focus on EN/SE first, reactivate others one-by-one |

---

## User Preferences

- **Code Style:** Decomposition — keep scripts short, modular files
- **Security:** Follow strict security guidelines (see user rules)

---

## Commands & Scripts

```bash
# No specific commands documented yet
```

---

## TODO / Roadmap

- [ ] Items to be added as work progresses

---

## Languages

> **Reactivated on:** 2026-07-29  
> **Active languages:** English (en), Svenska (sv), Norsk (no), Dansk (dk), Suomi (fi), Íslenska (is)

### Polylang

All six languages exist as `language` terms plus their matching `pll_<slug>`
`term_language` companion terms. Slugs deliberately match the theme's URL paths
(`/sv/`, `/no/`, `/dk/`, `/fi/`, `/is/`), which is why Denmark uses `dk` (not
`da`) and Norway `no` (locale `nb_NO`).

| Language | Slug | Locale | Flag code |
|---|---|---|---|
| English | `en` | `en_US` | `us` |
| Svenska | `sv` | `sv_SE` | `se` |
| Norsk | `no` | `nb_NO` | `no` |
| Dansk | `dk` | `da_DK` | `dk` |
| Suomi | `fi` | `fi` | `fi` |
| Íslenska | `is` | `is_IS` | `is` |

The four new languages start with **0 translated posts**. Content still has to be
translated before those subsites are useful.

### Translation is done outside WordPress

The in-site AI translation was removed on 2026-07-29 (`inc/openai-translator.php`
and `inc/acf-translate-metabox.php` deleted, along with the translate branches in
`inc/network-cloner.php`). Translations are now produced externally and written
in over MCP. The theme makes no outbound AI calls.

Leftover orphaned rows in `wp_options`: `deepseek_api_key`, `openai_api_key`,
`translator_provider`, `translator_model`, `openai_model`. Nothing reads them.

### Switcher

The header/footer switcher shows **native language names**, not currency codes.
The underlying `data-currency` keys (`usd`, `sek`, `nok`, `dkk`, `eur`, `isk`)
are unchanged — they still drive price formatting in
`front-page/js/currency.js`, where each entry now carries a `name` used for the
visible label.

Files holding the switcher markup:

| File | Role |
|------|------|
| `front-page/sections/header.php` | Main header + mobile menu (included everywhere via `inc/universal-header.php`) |
| `front-page/sections/footer.php` | Footer dropdown |
| `front-page/sections/offer-header.php` | Offer landing page header |
| `front-page/js/currency.js` | Currency data, labels, redirects |

### Auto-redirect: still Sweden-only

`inc/geo-redirect.php` (`$redirect_map`) and `checkForSmartRedirect()` in
`currency.js` deliberately still redirect **only** Swedish visitors. Enabling
NO/DK/FI/IS there would drop visitors on empty subsites. Re-enable the
`LANG-DISABLED` lines in `inc/geo-redirect.php` once those languages have
translated content.

---

## Change Log

| Date | Change |
|------|--------|
| 2026-01-25 | Created Project_dyali.md as project memory file |
| 2026-01-25 | Disabled non-English/Swedish languages (NO, DK, FI, IS) |
| 2026-07-29 | Re-enabled NO, DK, FI, IS; switcher now shows language names instead of currency codes; added the 4 languages to Polylang |
