import { BaseSection } from './BaseSection.ts';
import { HeroSection } from './hero/HeroSection.ts';
import { FeaturesSection } from './features/FeaturesSection.ts';
import { CTASection } from './cta/CTASection.ts';
import { StatsSection } from './stats/StatsSection.ts';
import { NavbarSection } from './navbar/NavbarSection.ts';

const sectionMap: Record<string, new (el: HTMLElement) => BaseSection> = {
  hero: HeroSection,
  features: FeaturesSection,
  cta: CTASection,
  stats: StatsSection,
  navbar: NavbarSection,
};

export class SectionLoader {
  static loadSections() {
    const sectionElements = document.querySelectorAll('[data-section]');
    
    sectionElements.forEach((el) => {
      const sectionName = el.getAttribute('data-section');
      if (sectionName && sectionMap[sectionName]) {
        const SectionClass = sectionMap[sectionName];
        const sectionInstance = new SectionClass(el as HTMLElement);
        sectionInstance.init();
      }
    });
  }
}
