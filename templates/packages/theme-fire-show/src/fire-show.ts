/**
 * Fire Show Theme - Interactive Features
 * Based on ohniva-show-v3.html reference
 */

import Lenis from 'lenis';

export default function initFireShowTheme() {
  // Wait for DOM
  if (typeof document === 'undefined') return;

  // ── LENIS SMOOTH SCROLL ──
  const lenis = new Lenis({
    duration: 1.6,
    easing: (t: number) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
    smooth: true,
  });

  function raf(time: number) {
    lenis.raf(time);
    requestAnimationFrame(raf);
  }
  requestAnimationFrame(raf);

  // ── PROGRESS BAR ──
  const progressBar = document.querySelector('.fs-progress') as HTMLElement | null;
  if (progressBar) {
    lenis.on('scroll', ({ progress }: { progress: number }) => {
      progressBar.style.width = `${progress * 100}%`;
    });
  }

  // ── NAV SCROLL CLASS ──
  const navbar = document.querySelector('.navbar') as HTMLElement | null;
  if (navbar) {
    lenis.on('scroll', ({ scroll }: { scroll: number }) => {
      navbar.classList.toggle('fs-scrolled', scroll > 80);
    });
  }

  // ── HERO PARALLAX ──
  const heroParallax = document.querySelector('.hero-section .fs-parallax') as HTMLElement | null;
  if (heroParallax) {
    lenis.on('scroll', ({ scroll }: { scroll: number }) => {
      heroParallax.style.transform = `translateY(${scroll * 0.3}px)`;
    });
  }

  // ── CUSTOM CURSOR ──
  const cursor = document.querySelector('.fs-cursor') as HTMLElement | null;
  const cursorRing = document.querySelector('.fs-cursor-ring') as HTMLElement | null;

  if (cursor && cursorRing) {
    let mx = 0, my = 0;
    let rx = 0, ry = 0;
    let started = false;

    document.addEventListener('mousemove', (e: MouseEvent) => {
      mx = e.clientX;
      my = e.clientY;
      cursor.style.left = `${mx}px`;
      cursor.style.top = `${my}px`;

      if (!started) {
        rx = mx;
        ry = my;
        cursor.style.opacity = '1';
        cursorRing.style.opacity = '1';
        started = true;
      }
    });

    // Add hover class to interactive elements
    const hoverTargets = 'a, button, .card, .srv, .fs-service-item, .fs-hcard, .btn, input, textarea, select';
    document.querySelectorAll(hoverTargets).forEach((el) => {
      el.addEventListener('mouseenter', () => {
        cursor.classList.add('hovering');
        cursorRing.classList.add('hovering');
      });
      el.addEventListener('mouseleave', () => {
        cursor.classList.remove('hovering');
        cursorRing.classList.remove('hovering');
      });
    });

    // Smooth follow with lerp
    function lerpCursor() {
      rx += (mx - rx) * 0.1;
      ry += (my - ry) * 0.1;
      cursorRing.style.left = `${rx}px`;
      cursorRing.style.top = `${ry}px`;
      requestAnimationFrame(lerpCursor);
    }
    lerpCursor();
  }

  // ── HORIZONTAL SCROLL DRAG ──
  const hscrollTrack = document.querySelector('.fs-hscroll-track') as HTMLElement | null;
  if (hscrollTrack) {
    let isDragging = false;
    let startX = 0;
    let scrollLeft = 0;

    hscrollTrack.addEventListener('mousedown', (e: MouseEvent) => {
      isDragging = true;
      startX = e.pageX - hscrollTrack.offsetLeft;
      scrollLeft = hscrollTrack.scrollLeft;
      hscrollTrack.style.cursor = 'grabbing';
    });

    document.addEventListener('mouseup', () => {
      isDragging = false;
      if (hscrollTrack) hscrollTrack.style.cursor = 'grab';
    });

    document.addEventListener('mousemove', (e: MouseEvent) => {
      if (!isDragging || !hscrollTrack) return;
      const x = e.pageX - hscrollTrack.offsetLeft;
      hscrollTrack.scrollLeft = scrollLeft - (x - startX) * 1.5;
    });
  }

  // ── INTERSECTION OBSERVER FOR REVEALS ──
  const revealTargets = document.querySelectorAll('section, .fs-hscroll-outer, .fs-reveal, .hero-section');
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        const target = entry.target;
        if (entry.isIntersecting) {
          target.classList.add('in');
          target.classList.remove('out');
        } else {
          target.classList.remove('in');
          const rect = target.getBoundingClientRect();
          if (rect.top < 0) {
            target.classList.add('out');
          } else {
            target.classList.remove('out');
          }
        }
      });
    },
    { threshold: 0.1 }
  );

  revealTargets.forEach((target) => observer.observe(target));

  // ── EXPOSE LENIS FOR EXTERNAL USE ──
  (window as unknown as { lenis: typeof lenis }).lenis = lenis;
}