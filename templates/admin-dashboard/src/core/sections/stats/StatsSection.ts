import { BaseSection } from '../BaseSection.ts';

export class StatsSection extends BaseSection {
  init(): void {
    this.animateCounters();
  }

  private animateCounters(): void {
    const statValues = this.element.querySelectorAll('.stat-item__value');
    
    this.observeSelf({ threshold: 0.5 }, () => {
      statValues.forEach(el => {
        const target = el as HTMLElement;
        const text = target.textContent || '';
        const num = parseInt(text.replace(/[^0-9]/g, ''));
        if (isNaN(num)) return;
        
        let start = 0;
        const duration = 2000;
        const startTime = performance.now();
        const suffix = text.replace(/[0-9]/g, '');

        const update = (currentTime: number) => {
          const elapsed = currentTime - startTime;
          const progress = Math.min(elapsed / duration, 1);
          
          // easeOutQuart
          const easeOut = 1 - Math.pow(1 - progress, 4);
          const current = Math.floor(easeOut * num);
          
          target.textContent = current + suffix;

          if (progress < 1) {
            requestAnimationFrame(update);
          } else {
            target.textContent = text;
          }
        };

        requestAnimationFrame(update);
      });
    });
  }
}
