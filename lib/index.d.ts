/**
 * TypeScript definitions for Kirby Collection Manager
 * Add this file to your project for better IntelliSense and type checking
 */

export interface CollectionManagerOptions {
  /** The route for your collection page (required) */
  contentRoute: string;
  /** Enable Isotope.js animations */
  useIsotope?: boolean;
  /** Options passed to Isotope constructor */
  isotopeOptions?: IsotopeOptions;
  /** Callback after content replacement */
  afterReplace?: () => void;
  /** Enable debug logging */
  debug?: boolean;
}

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

export interface ReplacementData {
  /** CSS selector for the container to replace content in */
  containerSelector: string;
  /** CSS selector for individual items */
  itemSelector: string;
  /** Optional inner selector for item content */
  itemInnerSelector?: string;
  /** HTML content to replace */
  data: string;
  /** Whether to replace outerHTML instead of innerHTML */
  outerHTML?: boolean;
  /** Whether to use Isotope for this replacement */
  isotope?: boolean;
}

export interface AjaxResponse {
  /** Array of content replacements to perform */
  replacements: ReplacementData[];
}

export interface TouchEndCallback {
  (link: HTMLAnchorElement): void;
}

export interface TaxonomyEventOptions {
  /** Callback for touch end events */
  onTouchEnd?: TouchEndCallback;
}

export declare class CollectionManager {
  /** Base route for the collection */
  readonly route: string;

  /** Isotope instances keyed by container selector */
  readonly isotopes: Record<string, any>;

  /** Whether debug mode is enabled */
  readonly isDebug: boolean;

  /** Isotope constructor (loaded dynamically) */
  Isotope?: any;

  /**
   * Creates a new CollectionManager instance
   * @param options Configuration options
   */
  constructor(options: CollectionManagerOptions);

  /**
   * Log a debug message (only if debug mode is enabled)
   * @param message The message to log
   * @param data Optional data to include
   */
  log(message: string, data?: any): void;

  /**
   * Log an error message
   * @param message The error message
   * @param error Optional error object
   */
  error(message: string, error?: any): void;

  /**
   * Get the base URL for AJAX requests
   */
  get baseUrl(): string;

  /**
   * Add click event listener to a pagination link
   * @param paginationLink The link element to listen to
   */
  listenPaginationEvent(paginationLink: HTMLElement): void;

  /**
   * Navigate to a specific page
   * @param pageNumber The page number to navigate to
   */
  paginate(pageNumber: string | number): Promise<void>;

  /**
   * Add click event listener to a taxonomy filter link
   * @param taxonomyLink The link element to listen to
   * @param options Additional options
   */
  listenTaxonomyEvent(taxonomyLink: HTMLElement, options?: TaxonomyEventOptions): void;

  /**
   * Toggle a URL parameter (add if not present, remove if present)
   * @param param The parameter name
   * @param value The parameter value
   */
  toggleParam(param: string, value: string): Promise<void>;

  /**
   * Add event listener to a search form
   * @param form The form element to listen to
   */
  listenSearchEvent(form: HTMLFormElement): void;

  /**
   * Perform a search
   * @param queryParam The query parameter name
   * @param query The search query
   */
  search(queryParam: string, query: string): Promise<void>;

  /**
   * Replace page content via AJAX
   * @param url The URL to fetch content from
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
