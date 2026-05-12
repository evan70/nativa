# Fire Show Theme - Add Interactive Features

## Reference
Based on `file:///home/evan/Desktop/HoTZ/ohniva-show-v3.html`

## Features to Add

### Phase 1: Core Visual Effects (Priority)

1. **Grain/noise overlay** ✅
   - SVG noise texture on body
   - Opacity ~7%

2. **Custom cursor** 
   - Fire-colored dot (6px)
   - Ring (32px) that expands to 56px on hover
   - Smooth lerp following

3. **Scroll progress bar**
   - Fixed at top, 1px height
   - Gradient from fire to gold
   - Driven by scroll position

4. **Smooth scroll (Lenis)**
   - Add Lenis library for smooth scrolling
   - Duration: 1.6s, easing

### Phase 2: Navigation

5. **Nav glassmorphism on scroll**
   - Starts transparent
   - Adds blur + background on scroll > 80px

### Phase 3: Hero Section

6. **Hero parallax glow**
   - Radial gradient moves with scroll
   - Uses Lenis scroll position

7. **Marquee animation**
   - Infinite scrolling text at hero bottom
   - "Fireshow — Žonglovanie — Svetelná show — ..."

8. **Fire gradient text**
   - Animated gradient on headings
   - Uses background-size: 200% + animation

### Phase 4: Interactive Sections

9. **Service list hover fill**
   - Slide-in background on hover
   - Color change on hover

10. **Horizontal scroll cards**
    - Draggable horizontal scroll
    - Hover effects with fire gradient line

### Phase 5: Scroll Animations

11. **Scroll-triggered text fill**
    - Large text fills with fire gradient as you scroll
    - Uses CSS animation-timeline

12. **Reveal animations**
    - Elements fade/slide in on scroll
    - IntersectionObserver based

## Implementation Notes

- Add TypeScript file in theme package
- Add Lenis as dependency
- Update CSS with new effects
- Use IntersectionObserver for reveals
- Fonts: Unbounded + Cormorant Garamond (already loaded in project)

## Files to Modify
- `templates/packages/theme-fire-show/src/assets/fire-show.css`
- Create: `templates/packages/theme-fire-show/src/fire-show.ts`
- Update `templates/packages/theme-fire-show/package.json` if needed
- Update Vite config to include new JS