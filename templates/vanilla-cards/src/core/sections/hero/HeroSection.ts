import { BaseSection } from '../BaseSection.ts';

export class HeroSection extends BaseSection {
  init(): void {
    this.initTitleReveal();
    this.initDescriptionFade();
  }

  private initTitleReveal(): void {
    const title = this.element.querySelector('.hero-section__title') as HTMLElement;
    if (!title) return;

    this.observe('.hero-section__title', { threshold: 0.3 }, (el) => {
      (el as HTMLElement).classList.add('is-visible');
    });
  }

  private initDescriptionFade(): void {
    const desc = this.element.querySelector('.hero-section__description') as HTMLElement;
    if (!desc) return;

    this.observe('.hero-section__description', { threshold: 0.3 }, (el) => {
      (el as HTMLElement).classList.add('is-visible');
    });
  }
}
