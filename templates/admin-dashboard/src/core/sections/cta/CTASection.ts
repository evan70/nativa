import { BaseSection } from '../BaseSection.ts';

export class CTASection extends BaseSection {
  init(): void {
    // Simple fade in for CTA
    this.element.style.opacity = '0';
    this.element.style.transition = 'opacity 0.8s ease';

    this.observeSelf({ threshold: 0.2 }, () => {
      this.element.style.opacity = '1';
    });
  }
}
