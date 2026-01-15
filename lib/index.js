/**
 * Collection Manager - htmx-based implementation
 *
 * This is a minimal JavaScript module for the Collection Manager plugin.
 * All AJAX functionality is now handled by htmx via HTML attributes.
 *
 * This file only exports the IsotopeManager for optional masonry layouts.
 * For the legacy JavaScript-based AJAX implementation, see index.legacy.js
 */

export { IsotopeManager } from './isotope.js';

/**
 * htmx Configuration Helpers
 *
 * These utilities can be used to customize htmx behavior
 */

/**
 * Configure htmx settings for the collection manager
 */
export function configureHtmx(options = {}) {
  if (typeof htmx === 'undefined') {
    console.warn('[CollectionManager] htmx is not loaded');
    return;
  }

  // Apply default configuration
  const defaults = {
    // Use history API for URL updates
    historyEnabled: true,
    // Default timeout for requests
    timeout: 10000,
    // Show loading indicator
    indicatorClass: 'htmx-indicator',
    ...options
  };

  // Configure htmx
  htmx.config.timeout = defaults.timeout;
  htmx.config.historyCacheSize = 10;
  htmx.config.refreshOnHistoryMiss = true;

  return defaults;
}

/**
 * Add loading indicator to collection manager
 */
export function addLoadingIndicator(selector = '#collection-content') {
  const container = document.querySelector(selector);
  if (!container) return;

  // Create loading overlay
  const indicator = document.createElement('div');
  indicator.className = 'collection-loading htmx-indicator';
  indicator.innerHTML = `
    <div class="collection-loading__spinner"></div>
    <span class="collection-loading__text">Loading...</span>
  `;

  // Add styles if not already present
  if (!document.querySelector('#collection-loading-styles')) {
    const styles = document.createElement('style');
    styles.id = 'collection-loading-styles';
    styles.textContent = `
      .collection-loading {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        display: none;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 1rem;
        z-index: 100;
      }
      .htmx-request .collection-loading,
      .htmx-request.collection-loading {
        display: flex;
      }
      .collection-loading__spinner {
        width: 2rem;
        height: 2rem;
        border: 3px solid #e5e5e5;
        border-top-color: #333;
        border-radius: 50%;
        animation: collection-spin 0.8s linear infinite;
      }
      @keyframes collection-spin {
        to { transform: rotate(360deg); }
      }
    `;
    document.head.appendChild(styles);
  }

  container.style.position = 'relative';
  container.appendChild(indicator);

  return indicator;
}

/**
 * Initialize collection manager with htmx
 * Call this after the page loads to set up any additional behaviors
 */
export function init(options = {}) {
  // Configure htmx
  configureHtmx(options);

  // Add loading indicator if enabled
  if (options.loadingIndicator !== false) {
    addLoadingIndicator(options.target || '#collection-content');
  }

  // Listen for htmx events
  document.body.addEventListener('htmx:beforeRequest', (event) => {
    // Add loading class to container
    const target = event.detail.target;
    if (target) {
      target.classList.add('htmx-loading');
    }
  });

  document.body.addEventListener('htmx:afterRequest', (event) => {
    // Remove loading class
    const target = event.detail.target;
    if (target) {
      target.classList.remove('htmx-loading');
    }
  });

  // Scroll to top after swap (optional)
  if (options.scrollToTop !== false) {
    document.body.addEventListener('htmx:afterSwap', (event) => {
      const container = document.querySelector('#collection-manager');
      if (container && event.detail.target?.closest('#collection-content')) {
        container.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  }

  console.log('[CollectionManager] Initialized with htmx');
}

// Auto-initialize if data attribute present
document.addEventListener('DOMContentLoaded', () => {
  const manager = document.querySelector('[data-collection-manager]');
  if (manager && manager.dataset.autoInit !== 'false') {
    init({
      loadingIndicator: manager.dataset.loadingIndicator !== 'false',
      scrollToTop: manager.dataset.scrollToTop !== 'false'
    });
  }
});
