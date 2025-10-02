export class CollectionManager {

  route = null;
  isotopes = new Map(); // Use Map for better memory management
  isDebug = false;
  abortController = null; // For cancelling requests
  eventListeners = new WeakMap(); // Track event listeners for cleanup

  constructor({
    contentRoute,
    useIsotope = false,
    isotopeOptions = {},
    afterReplace = () => { },
    debug = false,
    paginationParam = 'p',
    searchParam = 'q'
  }) {
    // Enhanced validation
    if (!contentRoute || typeof contentRoute !== 'string') {
      throw new Error('CollectionManager: contentRoute must be a non-empty string');
    }

    if (typeof useIsotope !== 'boolean') {
      throw new Error('CollectionManager: useIsotope must be a boolean');
    }

    if (isotopeOptions && typeof isotopeOptions !== 'object') {
      throw new Error('CollectionManager: isotopeOptions must be an object');
    }

    if (typeof afterReplace !== 'function') {
      throw new Error('CollectionManager: afterReplace must be a function');
    }

    this.route = contentRoute.replace(/\/$/, ''); // Remove trailing slash
    this.useIsotope = useIsotope;
    this.isotopeOptions = { ...isotopeOptions }; // Clone to prevent external mutations
    this.afterReplace = afterReplace;
    this.isDebug = Boolean(debug);
    this.paginationParam = paginationParam;
    this.searchParam = searchParam;

    this.log('CollectionManager initialized', { contentRoute, useIsotope, paginationParam, searchParam });
  }

  log(message, data = null) {
    if (this.isDebug) {
      console.log(`[CollectionManager] ${message}`, data || '');
    }
  }

  error(message, error = null) {
    console.error(`[CollectionManager] ${message}`, error || '');
  }

  /**
   * Validate that an element is a valid DOM element
   */
  validateElement(element, elementName = 'element') {
    if (!element || !(element instanceof Element)) {
      throw new Error(`${elementName} must be a valid DOM element`);
    }
  }

  /**
   * Validate page number
   */
  validatePageNumber(pageNumber) {
    const num = parseInt(pageNumber, 10);
    if (isNaN(num) || num < 1) {
      throw new Error(`Invalid page number: ${pageNumber}`);
    }
    return num;
  }

  /**
   * Sanitize URL parameters
   */
  sanitizeParam(param, value) {
    if (typeof param !== 'string' || param.trim() === '') {
      throw new Error('Parameter name must be a non-empty string');
    }
    if (typeof value !== 'string') {
      throw new Error('Parameter value must be a string');
    }
    return {
      param: param.trim(),
      value: value.trim()
    };
  }

  /**
   * Clean up resources and event listeners
   */
  destroy() {
    // Cancel any pending requests
    if (this.abortController) {
      this.abortController.abort();
    }

    // Clean up Isotope instances
    this.isotopes.forEach((isotope, key) => {
      if (isotope && typeof isotope.destroy === 'function') {
        isotope.destroy();
      }
    });
    this.isotopes.clear();

    this.log('CollectionManager destroyed');
  }

  /**
   * Validate replacement data structure
   */
  validateReplacement(replacement, index) {
    if (!replacement || typeof replacement !== 'object') {
      throw new Error(`Replacement ${index} must be an object`);
    }

    if (!replacement.containerSelector || typeof replacement.containerSelector !== 'string') {
      throw new Error(`Replacement ${index} must have a valid containerSelector string`);
    }

    if (replacement.outerHTML !== true) {
      if (!replacement.itemSelector || typeof replacement.itemSelector !== 'string') {
        throw new Error(`Replacement ${index} must have a valid itemSelector string when outerHTML is not true`);
      }
    }

    if (!replacement.data || typeof replacement.data !== 'string') {
      throw new Error(`Replacement ${index} must have valid data string`);
    }
  }

  get baseUrl() {
    // If route is already a full URL, return it as-is
    if (this.route.startsWith('http://') || this.route.startsWith('https://')) {
      return this.route;
    }
    // Otherwise, construct full URL from origin and path
    return `${window.location.origin}${this.route}`;
  }

  listenPaginationEvent(paginationLink) {
    this.validateElement(paginationLink, 'paginationLink');

    // Store reference for potential cleanup
    const handler = async (event) => {
      event.preventDefault();
      const link = event.target.closest('a');
      if (!link || !link.hasAttribute('data-page')) {
        this.log('Pagination link clicked but no data-page attribute found');
        return;
      }
      const pageNumber = link.getAttribute('data-page');
      this.log('Pagination clicked', { pageNumber });
      await this.paginate(pageNumber);
    };

    paginationLink.addEventListener('click', handler);
    this.eventListeners.set(paginationLink, { click: handler });
  }

  async paginate(pageNumber) {
    try {
      const validPageNumber = this.validatePageNumber(pageNumber);

      // Handle first page only elements
      document.querySelectorAll('[data-first-page-only]')
        .forEach(element => {
          element.style.display = validPageNumber === 1 ? 'block' : 'none';
        });

      const currentUrlParams = new URLSearchParams(window.location.search);
      if (validPageNumber === 1) {
        // For page 1, remove the page parameter
        currentUrlParams.delete(this.paginationParam);
      } else {
        // For other pages, set the page parameter
        currentUrlParams.set(this.paginationParam, validPageNumber);
      }

      // Add JSON parameter for AJAX requests
      currentUrlParams.set('json', '1');

      const queryString = currentUrlParams.toString();
      const url = queryString ? `${this.baseUrl}?${queryString}` : `${this.baseUrl}?json=1`;

      await this.replaceContent(url);
    } catch (error) {
      this.error('Error during pagination', error);
      throw error; // Re-throw for proper error handling
    }
  }  listenTaxonomyEvent(taxonomyLink, { onTouchEnd } = {}) {
    this.validateElement(taxonomyLink, 'taxonomyLink');

    if (onTouchEnd && typeof onTouchEnd !== 'function') {
      throw new Error('onTouchEnd must be a function');
    }

    const clickHandler = async (event) => {
      event.preventDefault();
      const link = event.target.closest('a');
      if (!link || !link.hasAttribute('data-param') || !link.hasAttribute('data-value')) {
        this.log('Taxonomy link clicked but missing data-param or data-value attributes');
        return;
      }
      const param = link.getAttribute('data-param');
      const value = link.getAttribute('data-value');
      this.log('Taxonomy filter clicked', { param, value });
      await this.toggleParam(param, value);
    };

    taxonomyLink.addEventListener('click', clickHandler);

    const listeners = { click: clickHandler };

    if (onTouchEnd) {
      const touchHandler = async (event) => {
        const link = event.target.closest('a');
        onTouchEnd(link);
      };
      taxonomyLink.addEventListener('touchend', touchHandler);
      listeners.touchend = touchHandler;
    }

    this.eventListeners.set(taxonomyLink, listeners);
  }

  async toggleParam(param, value) {
    try {
      const sanitized = this.sanitizeParam(param, value);

      const currentUrlParams = new URLSearchParams(window.location.search);
      if (currentUrlParams.get(sanitized.param) === sanitized.value) {
        currentUrlParams.delete(sanitized.param);
        document.querySelectorAll(`[data-first-page-only]`)
          .forEach(element => {
            element.style.display = 'block';
          });
      } else {
        currentUrlParams.set(sanitized.param, sanitized.value);
        document.querySelectorAll(`[data-first-page-only]`)
          .forEach(element => {
            element.style.display = 'none';
          });
      }
      if (currentUrlParams.get(this.paginationParam)) {
        /** Remove pagination */
        currentUrlParams.delete(this.paginationParam);
      }
      // Add JSON parameter for AJAX requests
      currentUrlParams.set('json', '1');
      const url = `${this.baseUrl}?${currentUrlParams.toString()}`;
      await this.replaceContent(url);
    } catch (error) {
      this.error('Error during parameter toggle', error);
      throw error;
    }
  }

  listenSearchEvent(form) {
    this.validateElement(form, 'form');

    if (form.tagName.toLowerCase() !== 'form') {
      throw new Error('Element must be a form element');
    }

    const submitHandler = async (event) => {
      event.preventDefault();
      const search = form.querySelector('input[type="search"]');
      const queryParam = search?.getAttribute('name');
      if (queryParam && search?.value) {
        this.log('Search submitted', { queryParam, value: search.value });
        await this.search(queryParam, search.value);
      } else {
        this.log('Search form submitted but missing query parameter or value');
      }
    };

    const submit = form.querySelector('input[type="submit"], button[type="submit"]');
    if (submit) {
      submit.addEventListener('click', submitHandler);
      this.eventListeners.set(submit, { click: submitHandler });
    }

    // Also listen for form submit events
    form.addEventListener('submit', submitHandler);
    this.eventListeners.set(form, { submit: submitHandler });
  }

  async search(queryParam, query) {
    try {
      const sanitized = this.sanitizeParam(queryParam, query);

      const currentUrlParams = new URLSearchParams(window.location.search);
      currentUrlParams.set(sanitized.param, sanitized.value);
      if (currentUrlParams.get(this.paginationParam)) {
        /** Remove pagination */
        currentUrlParams.delete(this.paginationParam);
      }
      // Add JSON parameter for AJAX requests
      currentUrlParams.set('json', '1');
      const url = `${this.baseUrl}?${currentUrlParams.toString()}`;
      await this.replaceContent(url);
    } catch (error) {
      this.error('Error during search', error);
      throw error;
    }
  }

  async replaceContent(url) {
    if (!url || typeof url !== 'string') {
      throw new Error('URL must be a non-empty string');
    }

    // Validate URL format
    try {
      new URL(url);
    } catch (e) {
      throw new Error(`Invalid URL format: ${url}`);
    }

    this.log('Replacing content from URL:', url);

    // Cancel any previous request
    if (this.abortController) {
      this.abortController.abort();
    }

    // Create new abort controller for this request
    this.abortController = new AbortController();

    try {
      /** Fetch response with timeout and abort signal */
      const response = await fetch(url, {
        signal: this.abortController.signal,
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      const contentType = response.headers.get('content-type');
      if (!contentType || !contentType.includes('application/json')) {
        throw new Error('Response is not valid JSON');
      }

      const data = await response.json();

      if (!data || typeof data !== 'object') {
        throw new Error('Invalid response data format');
      }

      if (!data.replacements || !Array.isArray(data.replacements)) {
        this.log('No replacements found in response');
        return;
      }

      /** Replace content */
      const processedContainers = new Set();

      data.replacements.forEach((replacement, index) => {
        try {
          this.validateReplacement(replacement, index);

          const container = document.querySelector(replacement.containerSelector);
          if (!container) {
            this.error(`Container not found: ${replacement.containerSelector}`);
            return;
          }

          // Prevent duplicate processing of the same container
          if (processedContainers.has(replacement.containerSelector)) {
            this.log(`Skipping duplicate replacement for container: ${replacement.containerSelector}`);
            return;
          }
          processedContainers.add(replacement.containerSelector);

          if (replacement.outerHTML === true) {
            container.outerHTML = replacement.data;
            return;
          }

          const itemSelector = replacement.itemSelector;
          const itemInnerSelector = replacement.itemInnerSelector ?? null;

          /** Parse new items */
          const parser = new DOMParser();
          const doc = parser.parseFromString(replacement.data, 'text/html');

          /** Update existing items with new data attributes (like data-order) */
          const existingItems = [...container.querySelectorAll(itemSelector)];
          existingItems.forEach(existingGridItem => {
            const existingItem = itemInnerSelector ? existingGridItem.querySelector(itemInnerSelector) : existingGridItem;
            const id = existingItem?.dataset.id;

            if (id) {
              // Find corresponding item in new data
              const newGridItem = [...doc.querySelectorAll(itemSelector)].find(gridItem => {
                const newItem = itemInnerSelector ? gridItem.querySelector(itemInnerSelector) : gridItem;
                return newItem?.dataset.id === id;
              });

              if (newGridItem) {
                const newItem = itemInnerSelector ? newGridItem.querySelector(itemInnerSelector) : newGridItem;
                // Update data-order and other data attributes
                if (newItem?.dataset.order !== undefined) {
                  existingItem.dataset.order = newItem.dataset.order;
                }
              }
            }
          });

          /** Compute items to add and remove */
          const itemsToAdd = [...doc.querySelectorAll(itemSelector)]
            .filter(gridItem => {
              const item = itemInnerSelector ? gridItem.querySelector(itemInnerSelector) : gridItem;
              const id = item?.dataset.id;
              return id && !container.querySelector(`[data-id="${id}"]`);
            });

          const itemsToRemove = [...container.querySelectorAll(itemSelector)]
            .filter(gridItem => {
              const item = itemInnerSelector ? gridItem.querySelector(itemInnerSelector) : gridItem;
              const id = item?.dataset.id;
              return id && !doc.querySelector(`[data-id="${id}"]`);
            });

          this.log('Content replacement', {
            itemsToAdd: itemsToAdd.length,
            itemsToRemove: itemsToRemove.length
          });

          if (this.useIsotope && replacement.isotope) {
            /** Initialize isotope if not already done */
            const key = replacement.containerSelector;
            if (!this.isotopes.has(key)) {
              if (!this.Isotope) {
                throw new Error('Isotope library not loaded. Call loadIsotope() first.');
              }

              const isotopeInstance = new this.Isotope(container, {
                itemSelector,
                sortBy: 'order',
                getSortData: {
                  order: function (element) {
                    const item = itemInnerSelector ? element.querySelector(itemInnerSelector) : element;
                    return parseInt(item?.dataset?.order || 0, 10);
                  }
                },
                ...this.isotopeOptions,
              });
              this.isotopes.set(key, isotopeInstance);
            }
            this.isotopeReplace(this.isotopes.get(key), itemsToAdd, itemsToRemove);
          } else {
            this.replace(container, itemsToAdd, itemsToRemove, itemInnerSelector);
          }
        } catch (replacementError) {
          this.error('Error processing replacement', replacementError);
        }
      });

      /** Eventually scroll */
      const top = document.querySelector("[data-replacementtop='true']");
      if (top) {
        const offset = parseInt(top.getAttribute('data-offset') || '0', 10);
        if (isNaN(offset)) {
          this.error('Invalid data-offset value, using 0');
        }
        window.scrollTo({
          top: Math.max(0, top.offsetTop - (isNaN(offset) ? 0 : offset)),
          behavior: 'smooth'
        });
      }

      /** Update url in browser */
      try {
        const urlParts = url.split('?');
        const jsonUrlParams = new URLSearchParams(urlParts[1] || '');
        const newUrl = `${window.location.pathname}?${jsonUrlParams.toString()}`;
        window.history.pushState({}, '', newUrl);
      } catch (historyError) {
        this.error('Failed to update browser history', historyError);
      }

      this.afterReplace();
      this.log('Content replacement completed successfully');

    } catch (error) {
      this.error('Error during content replacement:', error);

      // Fallback: navigate to the URL if AJAX fails
      if (error.name !== 'AbortError' && typeof window !== 'undefined') {
        this.log('Falling back to page navigation');
        window.location.href = url;
      }
    } finally {
      // Clear the abort controller
      this.abortController = null;
    }
  }

  replace(container, itemsToAdd, itemsToRemove, itemInnerSelector) {
    if (!container || !(container instanceof Element)) {
      throw new Error('Container must be a valid DOM element');
    }

    if (!Array.isArray(itemsToAdd) || !Array.isArray(itemsToRemove)) {
      throw new Error('itemsToAdd and itemsToRemove must be arrays');
    }

    /** Remove items */
    itemsToRemove.forEach(item => {
      if (item && item.parentNode) {
        item.remove();
      }
    });

    /** Add new items first */
    itemsToAdd.forEach(gridItem => {
      try {
        container.appendChild(gridItem);
      } catch (insertError) {
        this.error('Error inserting item', insertError);
      }
    });

    /** Now reorder all items in container based on data-order */
    this.reorderItems(container, itemInnerSelector);
  }

  /**
   * Reorder all items in container based on their data-order attributes
   */
  reorderItems(container, itemInnerSelector) {
    const items = [...container.children];

    // Sort items by their data-order attribute
    items.sort((a, b) => {
      const itemA = itemInnerSelector ? a.querySelector(itemInnerSelector) : a;
      const itemB = itemInnerSelector ? b.querySelector(itemInnerSelector) : b;

      const orderA = parseInt(itemA?.dataset?.order || '0', 10);
      const orderB = parseInt(itemB?.dataset?.order || '0', 10);

      return orderA - orderB;
    });

    // Reorder items in the DOM
    items.forEach(item => {
      container.appendChild(item);
    });
  }

  async loadIsotope() {
    try {
      if (this.Isotope) {
        this.log('Isotope already loaded');
        return;
      }

      const isotopeModule = await import('isotope-layout');
      this.Isotope = isotopeModule.default || isotopeModule;
      this.log('Isotope library loaded successfully');
    } catch (error) {
      this.error('Failed to load Isotope library', error);
      throw new Error(`Failed to load Isotope: ${error.message}`);
    }
  }

  isotopeReplace(isotope, itemsToAdd, itemsToRemove) {
    if (!isotope) {
      this.error('Isotope instance not provided');
      return;
    }

    if (!Array.isArray(itemsToAdd) || !Array.isArray(itemsToRemove)) {
      this.error('itemsToAdd and itemsToRemove must be arrays');
      return;
    }

    try {
      /** Add and remove items, then layout */
      if (itemsToRemove.length > 0) {
        isotope.remove(itemsToRemove);
      }
      if (itemsToAdd.length > 0) {
        isotope.insert(itemsToAdd);
      }
      // Uncomment the next line if you want to trigger layout manually
      // isotope.layout();
    } catch (isotopeError) {
      this.error('Error during Isotope operation', isotopeError);
    }
  }
}
