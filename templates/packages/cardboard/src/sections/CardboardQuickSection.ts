import { BaseSection } from '../../../../src/core/sections/BaseSection.ts';

export class CardboardQuickSection extends BaseSection {
  init(): void {
    // Add any initialization logic for the quick stats section
    // For example, set up counters or animate numbers

    // Example: Animate elements on scroll
    const animatedElements = this.element.querySelectorAll('.cardboard-quick__animate');
    animatedElements.forEach((el) => {
      this.observe(el, { threshold: 0.3 }, (element) => {
        (element as HTMLElement).classList.add('is-visible');
      });
    });
  }
}