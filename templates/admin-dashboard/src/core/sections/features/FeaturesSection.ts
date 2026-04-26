import { BaseSection } from '../BaseSection.ts';

export class FeaturesSection extends BaseSection {
  init(): void {
    this.initCardsStagger();
  }

  private initCardsStagger(): void {
    const cards = this.element.querySelectorAll('.card');
    if (!cards.length) return;

    cards.forEach((card, index) => {
      const el = card as HTMLElement;
      el.style.opacity = '0';
      el.style.transform = 'translateY(20px)';
      el.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
    });

    this.observeSelf({ threshold: 0.1 }, () => {
      cards.forEach((card) => {
        const el = card as HTMLElement;
        el.style.opacity = '1';
        el.style.transform = 'translateY(0)';
      });
    });
  }
}
