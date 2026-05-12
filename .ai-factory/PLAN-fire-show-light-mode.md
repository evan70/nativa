# Fix Fire Show Theme - Light Mode Colors

## Problem
The fire-show theme has incorrect light mode colors. Need to match reference site's color palette (warm fire theme).

## Reference Colors (from ohniva-pricing.html)
Dark mode:
- --bg: #060504
- --fg: #ede8df  
- --fire: #e8622a
- --gold: #c49a3c
- --warm: #c8b99a
- --dim: #2e2620

## Tasks

### 1. Fix light mode CSS variables
Update `[data-theme="light"]` in `templates/packages/theme-fire-show/src/assets/fire-show.css`:

**Light mode should mirror dark mode palette but lighter:**
- Base colors on reference dark mode: #060504 (bg), #ede8df (fg), #e8622a (fire), #c49a3c (gold)
- Use warm white backgrounds
- Keep fire/gold theme but adjusted for readability on light
- Proper contrast for text

**Target colors:**
```css
[data-theme="light"] {
  /* Warm white backgrounds - lighter version of dark #060504 */
  --color-bg: #fff8f5;
  --color-bg-alt: #ffffff;
  --color-bg-hover: #ffeae0;
  --color-border: #ffdcc8;
  
  /* Dark warm text - lighter version of dark #ede8df */
  --color-text: #2a1810;
  --color-text-muted: #6b4a40;
  --color-text-inverse: #ffffff;
  
  /* Fire colors - adjusted for light backgrounds */
  --brand-ruby: #d94a1a;  /* Brighter fire red */
  --brand-ruby-light: #e8622a;
  --brand-ruby-dark: #b83d10;
  --brand-gold: #c49a3c; /* Same as reference */
  --brand-gold-light: #d4a840;
  
  /* Shadows - warm fire glow but lighter */
  --shadow-xs: 0 0 5px rgba(217, 74, 26, 0.08);
  --shadow-sm: 0 2px 8px rgba(217, 74, 26, 0.12);
  --shadow-md: 0 4px 15px rgba(217, 74, 26, 0.18);
  --shadow-lg: 0 8px 25px rgba(217, 74, 26, 0.22);
  --shadow-xl: 0 12px 35px rgba(217, 74, 26, 0.28);
}

### 2. Test light mode
- Verify theme switch works
- Check all components render correctly in light mode
- Verify contrast ratios

### 3. Commit and push

## Notes
- Only affects development (light mode toggle)
- No production impact
- Testing: use theme switcher in navbar