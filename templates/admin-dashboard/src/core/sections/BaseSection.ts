export abstract class BaseSection {
  constructor(protected element: HTMLElement) {}

  abstract init(): void;

  protected observe(
    selector: string,
    options: IntersectionObserverInit,
    callback: (element: Element, entry: IntersectionObserverEntry) => void,
    immediate = true
  ): void {
    const elements = this.element.querySelectorAll(selector);
    if (!elements.length) return;

    const observer = new IntersectionObserver((entries, obs) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          callback(entry.target, entry);
          obs.unobserve(entry.target);
        }
      });
    }, options);

    elements.forEach(el => {
      observer.observe(el);
    });
  }

  protected observeSelf(
    options: IntersectionObserverInit,
    callback: (element: HTMLElement, entry: IntersectionObserverEntry) => void
  ): void {
    const observer = new IntersectionObserver((entries, obs) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          callback(this.element, entry);
          obs.unobserve(this.element);
        }
      });
    }, options);

    observer.observe(this.element);
  }
}
