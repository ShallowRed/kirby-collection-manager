/**
 * Isotope Integration Module for Collection Manager
 * Optional module for masonry/filtering layouts with htmx
 */

export class IsotopeManager {
  constructor({
    container = '.collection-items__list',
    itemSelector = '.collection-item',
    options = {}
  }) {
    this.containerSelector = container;
    this.itemSelector = itemSelector;
    this.options = {
      itemSelector: itemSelector,
      layoutMode: 'masonry',
      ...options
    };
    this.isotope = null;

    this.init();
  }

  async init() {
    // Dynamically import Isotope if not already loaded
    if (typeof Isotope === 'undefined') {
      try {
        const { default: Isotope } = await import('https://unpkg.com/isotope-layout@3/dist/isotope.pkgd.min.js');
        window.Isotope = Isotope;
      } catch (e) {
        console.warn('[IsotopeManager] Isotope library not available:', e);
        return;
      }
    }

    this.createInstance();
  }

  createInstance() {
    const container = document.querySelector(this.containerSelector);
    if (!container) {
      console.warn(`[IsotopeManager] Container not found: ${this.containerSelector}`);
      return;
    }

    // Destroy existing instance if any
    if (this.isotope) {
      this.isotope.destroy();
    }

    // Wait for images to load before initializing
    if (typeof imagesLoaded !== 'undefined') {
      imagesLoaded(container, () => {
        this.isotope = new Isotope(container, this.options);
      });
    } else {
      this.isotope = new Isotope(container, this.options);
    }
  }

  reinit() {
    // Re-initialize after htmx content swap
    setTimeout(() => {
      this.createInstance();
    }, 100);
  }

  layout() {
    if (this.isotope) {
      this.isotope.layout();
    }
  }

  filter(selector) {
    if (this.isotope) {
      this.isotope.arrange({ filter: selector });
    }
  }

  destroy() {
    if (this.isotope) {
      this.isotope.destroy();
      this.isotope = null;
    }
  }
}

// Auto-initialize if data attribute present
document.addEventListener('DOMContentLoaded', () => {
  const container = document.querySelector('[data-isotope]');
  if (container) {
    const options = JSON.parse(container.dataset.isotopeOptions || '{}');
    window.collectionIsotope = new IsotopeManager({
      container: container.dataset.isotope || '.collection-items__list',
      options
    });

    // Listen for htmx swaps
    document.body.addEventListener('htmx:afterSwap', () => {
      window.collectionIsotope?.reinit();
    });
  }
});
