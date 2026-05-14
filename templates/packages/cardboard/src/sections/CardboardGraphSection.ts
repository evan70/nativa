import { BaseSection } from '../../../../src/core/sections/BaseSection.ts';

export class CardboardGraphSection extends BaseSection {
  init(): void {
    // Add any initialization logic for the graph section
    // For example, initialize a chart or set up event listeners

    // Example: Set initial opacity for animation
    this.element.style.opacity = '0';
    this.element.style.transition = 'opacity 0.8s ease';

    // Observe when the section enters the viewport to animate in
    this.observeSelf({ threshold: 0.2 }, () => {
      this.element.style.opacity = '1';
    });
  }
}