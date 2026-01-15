/**
 * TypeScript definitions for Kirby Collection Manager
 * htmx-based implementation
 */

// ============================================================================
// Isotope Manager (Optional masonry layouts)
// ============================================================================

export interface IsotopeOptions {
  /** CSS selector for grid items */
  itemSelector?: string;
  /** Layout mode */
  layoutMode?: 'masonry' | 'fitRows' | 'cellsByRow' | 'packery' | 'horiz';
  /** Sort items by property */
  sortBy?: string | string[];
  /** Sort direction */
  sortAscending?: boolean;
  /** Filter items */
  filter?: string | ((element: Element) => boolean);
  /** Animation duration */
  transitionDuration?: string | number;
  /** Animation easing */
  hiddenStyle?: Record<string, any>;
  /** Visible style */
  visibleStyle?: Record<string, any>;
  /** Get sort data functions */
  getSortData?: Record<string, string | ((element: Element) => any)>;
  /** Column width */
  columnWidth?: number | string | Element;
  /** Row height */
  rowHeight?: number | string;
  /** Gutter */
  gutter?: number | string | Element;
  /** Fit width */
  fitWidth?: boolean;
  /** Origin left */
  originLeft?: boolean;
  /** Origin top */
  originTop?: boolean;
  /** Container style */
  containerStyle?: Record<string, any>;
  /** Resize */
  resize?: boolean;
  /** Initialize layout */
  initLayout?: boolean;
}

export interface IsotopeManagerOptions {
  /** CSS selector for the container */
  container?: string;
  /** CSS selector for items */
  itemSelector?: string;
  /** Isotope configuration options */
  options?: IsotopeOptions;
}

export declare class IsotopeManager {
  constructor(options: IsotopeManagerOptions);

  /** Initialize Isotope instance */
  init(): Promise<void>;

  /** Create Isotope instance on container */
  createInstance(): void;

  /** Re-initialize after content swap */
  reinit(): void;

  /** Trigger layout recalculation */
  layout(): void;

  /** Filter items by selector */
  filter(selector: string): void;

  /** Destroy Isotope instance */
  destroy(): void;
}

// ============================================================================
// htmx Configuration Helpers
// ============================================================================

export interface HtmxConfigOptions {
  /** Enable browser history for URL updates */
  historyEnabled?: boolean;
  /** Request timeout in milliseconds */
  timeout?: number;
  /** CSS class for loading indicator */
  indicatorClass?: string;
}

export interface CollectionManagerInitOptions extends HtmxConfigOptions {
  /** Show loading indicator during requests */
  loadingIndicator?: boolean;
  /** Scroll to top after content swap */
  scrollToTop?: boolean;
  /** Target container selector */
  target?: string;
}

/**
 * Configure htmx settings for the collection manager
 */
export declare function configureHtmx(options?: HtmxConfigOptions): HtmxConfigOptions;

/**
 * Add loading indicator to collection container
 */
export declare function addLoadingIndicator(selector?: string): HTMLElement | undefined;

/**
 * Initialize collection manager with htmx
 */
export declare function init(options?: CollectionManagerInitOptions): void;
   */
  replaceContent(url: string): Promise<void>;

  /**
   * Replace DOM elements in a container
   * @param container The container element
   * @param itemsToAdd Elements to add
   * @param itemsToRemove Elements to remove
   * @param itemInnerSelector Optional inner selector
   */
  replace(
    container: Element,
    itemsToAdd: Element[],
    itemsToRemove: Element[],
    itemInnerSelector?: string | null
  ): void;

  /**
   * Dynamically load the Isotope library
   */
  loadIsotope(): Promise<void>;

  /**
   * Replace items using Isotope
   * @param isotope The Isotope instance
   * @param itemsToAdd Elements to add
   * @param itemsToRemove Elements to remove
   */
  isotopeReplace(isotope: any, itemsToAdd: Element[], itemsToRemove: Element[]): void;
}

// Global declarations for usage without module imports
declare global {
  interface Window {
    CollectionManager: typeof CollectionManager;
  }
}

export default CollectionManager;
