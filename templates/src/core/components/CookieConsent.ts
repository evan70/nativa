import { NotificationManager } from './NotificationManager';

export class CookieConsent {
  private static readonly STORAGE_KEY = 'nativa-cookie-consent';

  static init(): void {
    try {
      const consent = localStorage.getItem(this.STORAGE_KEY);
      if (!consent) {
        this.showBanner();
      }
    } catch (e) {
      console.error('Failed to check cookie consent in localStorage', e);
    }
  }

  private static showBanner(): void {
    NotificationManager.show({
      title: 'Cookie Consent',
      message: 'We use cookies to improve your experience on our site and analyze traffic.',
      type: 'info',
      duration: 0,
      position: 'bottom-right',
      actions: [
        {
          label: 'Accept',
          primary: true,
          callback: () => {
            this.setConsent(true);
          }
        },
        {
          label: 'Privacy Policy',
          href: '/privacy-policy',
          primary: false
        }
      ]
    });
  }

  private static setConsent(granted: boolean): void {
    try {
      localStorage.setItem(this.STORAGE_KEY, granted.toString());
    } catch (e) {
      console.error('Failed to set cookie consent in localStorage', e);
    }
  }
}
