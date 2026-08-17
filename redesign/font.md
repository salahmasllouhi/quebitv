# Quebec IPTV - Font Configuration

---

## FONT OVERVIEW

| Role | Font Name | Weight | Type | Source |
|------|-----------|--------|------|--------|
| Headings | **Bricolage Grotesque** | 700 (Bold) | Sans-serif | Google Fonts |
| Body | **DM Sans** | 500 (Medium) | Sans-serif | Google Fonts |

---

## HEADING FONT: BRICOLAGE GROTESQUE

### Description
Bricolage Grotesque is a contemporary grotesque typeface with personality. It provides a modern, distinctive look perfect for headlines and titles.

### Weight Used
- **700 - Bold** (headings)
- **800 - Extra-Bold** (hero headlines)

### CSS Declaration
```css
font-family: 'Bricolage Grotesque', sans-serif;
font-weight: 700;
```

---

## BODY FONT: DM SANS

### Description
DM Sans is a geometric sans-serif font with clean, modern lines. Highly readable and versatile for both body text and UI elements.

### Weights Used
- **400 - Regular** (light text)
- **500 - Medium** (body text - default)
- **600 - Semi-Bold** (buttons, labels)
- **700 - Bold** (emphasis, prices)

### CSS Declaration
```css
font-family: 'DM Sans', sans-serif;
font-weight: 500;
```

---

## GOOGLE FONTS IMPORT

### HTML `<link>` Tag
```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
```

### CSS `@import`
```css
@import url('https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap');
```

---

## CSS VARIABLES

```css
:root {
    --font-heading: 'Bricolage Grotesque', sans-serif;
    --font-body: 'DM Sans', sans-serif;
}
```

---

## USAGE BY ELEMENT

| Element | Font | Weight |
|---------|------|--------|
| h1, h2, h3 | Bricolage Grotesque | 700 |
| Hero Headline | Bricolage Grotesque | 800 |
| Body Text | DM Sans | 500 |
| Buttons | DM Sans | 600 |
| Nav Links | DM Sans | 500 |
| Prices/Stats | DM Sans | 700 |
| Labels | DM Sans | 600 |

---

## COMPLETE CSS STYLES

```css
body {
    font-family: 'DM Sans', sans-serif;
    font-weight: 500;
    line-height: 1.6;
}

h1, h2, h3, h4, h5, h6 {
    font-family: 'Bricolage Grotesque', sans-serif;
    font-weight: 700;
    letter-spacing: -0.02em;
}

.btn {
    font-family: 'DM Sans', sans-serif;
    font-weight: 600;
}

.price, .stat-value {
    font-family: 'DM Sans', sans-serif;
    font-weight: 700;
}
```