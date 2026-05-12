# Demo Page for Fire Show Theme - 12 Features

## Goal
Create a demo page that showcases all 12 interactive features of the fire-show theme.

## Reference
Based on `file:///home/evan/Desktop/HoTZ/ohniva-show-v3.html`

## Features to Showcase

### 1. Grain Overlay
- Already in CSS, works automatically

### 2. Custom Cursor
- Fire-colored dot + ring that expands on hover
- Needs JS to be active

### 3. Scroll Progress Bar
- Fixed at top, gradient from fire to gold
- Needs JS to track scroll

### 4. Smooth Scroll (Lenis)
- Need to activate Lenis on the demo page

### 5. Nav Glassmorphism
- Nav becomes blurred on scroll
- Needs JS to track scroll position

### 6. Hero Parallax Glow
- Glow moves with scroll
- Needs JS

### 7. Marquee Animation
- Infinite scrolling text
- Pure CSS, works automatically

### 8. Fire Gradient Text
- Animated gradient on headings
- CSS class `.fs-text-fire`

### 9. Service List Hover Fill
- Slide-in fill effect on hover
- CSS classes: `.fs-service-list`, `.fs-service-item`

### 10. Horizontal Scroll Cards
- Draggable horizontal cards
- CSS + JS for drag functionality

### 11. Scroll Text Fill Animation
- Text fills with fire gradient on scroll
- CSS animation-timeline

### 12. Reveal Animations
- Elements animate in on scroll
- CSS classes + IntersectionObserver JS

## Tasks

### 1. Create Demo Page Template
Location: `templates/pages/fire-show-demo/`
- Create folder with page files
- Copy structure from existing page (e.g., home)

### 2. Add All 12 Features to Demo
- Hero section with fire gradient text
- Marquee below hero
- Service list with hover fill
- Horizontal scroll cards section
- Scroll text fill section
- Contact section with email hover
- All wrapped in reveal animations

### 3. Activate JavaScript Features
- Import fire-show.ts in the demo page
- Initialize all JS features (cursor, progress, lenis, etc.)

### 4. Build and Test
- Build templates
- Verify all features work

### 5. Commit and Push

## Notes
- Demo page should be at `/fire-show-demo` or similar route
- Fire-show theme features are already in: `templates/src/theme/fire-show/theme.css` and `templates/src/theme/fire-show.ts`
- Need to activate JS features specifically for this demo page