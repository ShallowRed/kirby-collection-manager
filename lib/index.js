export class CollectionManager {

  route = null;
  isotopes = {};
  isDebug = false;

  constructor({
    contentRoute,
    useIsotope = false,
    isotopeOptions = {},
    afterReplace = () => { },
    debug = false
  }) {
    // Validate required parameters
    if (!contentRoute) {
      throw new Error('CollectionManager: contentRoute is required');
    }
    
    this.route = contentRoute;
    this.useIsotope = useIsotope;
    this.isotopeOptions = isotopeOptions;
    this.afterReplace = afterReplace;
    this.isDebug = debug;
    
    this.log('CollectionManager initialized', { contentRoute, useIsotope });
  }

  log(message, data = null) {
    if (this.isDebug) {
      console.log(`[CollectionManager] ${message}`, data || '');
    }
  }

  error(message, error = null) {
    console.error(`[CollectionManager] ${message}`, error || '');
  }

  get baseUrl() {
    return `${window.location.origin}${this.route}`;
  }

  listenPaginationEvent(paginationLink) {
    if (!paginationLink || !paginationLink.addEventListener) {
      this.error('Invalid pagination link element provided');
      return;
    }

    paginationLink.addEventListener('click', async (event) => {
      event.preventDefault();
      const link = event.target.closest('a');
      if (!link || !link.hasAttribute('data-page')) {
        this.log('Pagination link clicked but no data-page attribute found');
        return;
      }
      const pageNumber = link.getAttribute('data-page');
      this.log('Pagination clicked', { pageNumber });
      await this.paginate(pageNumber);
    });
  }

  async paginate(pageNumber) {
    if (!pageNumber || pageNumber < 1) {
      this.error('Invalid page number provided', pageNumber);
      return;
    }

    try {
      // Handle first page only elements
      document.querySelectorAll('[data-first-page-only]')
        .forEach(element => {
          element.style.display = pageNumber === '1' ? 'block' : 'none';
        });
        
      const currentUrlParams = new URLSearchParams(window.location.search);
      currentUrlParams.set('p', pageNumber);
      await this.replaceContent(`${this.baseUrl}?${currentUrlParams.toString()}`);
    } catch (error) {
      this.error('Error during pagination', error);
    }
  }

  listenTaxonomyEvent(taxonomyLink, { onTouchEnd } = {}) {
    if (!taxonomyLink || !taxonomyLink.addEventListener) {
      this.error('Invalid taxonomy link element provided');
      return;
    }

    taxonomyLink.addEventListener('click', async (event) => {
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
    });
    
    if (onTouchEnd) {
      taxonomyLink.addEventListener('touchend', async (event) => {
        const link = event.target.closest('a');
        onTouchEnd(link);
      });
    }
  }

  async toggleParam(param, value) {
    if (!param || value === undefined) {
      this.error('Invalid param or value provided', { param, value });
      return;
    }

    try {
      const currentUrlParams = new URLSearchParams(window.location.search);
      if (currentUrlParams.get(param) === value) {
        currentUrlParams.delete(param);
        document.querySelectorAll(`[data-first-page-only]`)
          .forEach(element => {
            element.style.display = 'block';
          });
      } else {
        currentUrlParams.set(param, value);
        document.querySelectorAll(`[data-first-page-only]`)
          .forEach(element => {
            element.style.display = 'none';
          });
      }
      if (currentUrlParams.get('p')) {
        /** Remove pagination */
        currentUrlParams.delete('p');
      }
      const url = `${this.baseUrl}?${currentUrlParams.toString()}`;
      await this.replaceContent(url);
    } catch (error) {
      this.error('Error during parameter toggle', error);
    }
  }

  listenSearchEvent(form) {
    if (!form || !form.querySelector) {
      this.error('Invalid form element provided');
      return;
    }

    const submit = form.querySelector('input[type="submit"]');
    submit?.addEventListener('click', async (event) => {
      event.preventDefault();
      const search = form.querySelector('input[type="search"]');
      const queryParam = search?.getAttribute('name');
      if (queryParam && search?.value) {
        this.log('Search submitted', { queryParam, value: search.value });
        await this.search(queryParam, search.value);
      } else {
        this.log('Search form submitted but missing query parameter or value');
      }
    });
  }

  async search(queryParam, query) {
    if (!queryParam || !query) {
      this.error('Invalid search parameters', { queryParam, query });
      return;
    }

    try {
      const currentUrlParams = new URLSearchParams(window.location.search);
      currentUrlParams.set(queryParam, query);
      if (currentUrlParams.get('p')) {
        /** Remove pagination */
        currentUrlParams.delete('p');
      }
      const url = `${this.baseUrl}?${currentUrlParams.toString()}`;
      await this.replaceContent(url);
    } catch (error) {
      this.error('Error during search', error);
    }
  }

  async replaceContent(url) {
    if (!url) {
      this.error('No URL provided for content replacement');
      return;
    }

    this.log('Replacing content from URL:', url);
    
    try {
      /** Fetch response */
      const response = await fetch(url);
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      const data = await response.json();

      if (!data.replacements || !Array.isArray(data.replacements)) {
        this.log('No replacements found in response');
        return;
      }

      /** Replace content */
      data.replacements?.forEach(replacement => {
        try {
          const container = document.querySelector(replacement.containerSelector);
          if (!container) {
            this.error(`Container not found: ${replacement.containerSelector}`);
            return;
          }
          
          if (replacement.outerHTML === true) {
            container.outerHTML = replacement.data;
            return;
          }
          
          const itemSelector = replacement.itemSelector;
          const itemInnerSelector = replacement.itemInnerSelector ?? null;

          /** Parse new items */
          const parser = new DOMParser();
          const doc = parser.parseFromString(replacement.data, 'text/html');

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
            this.isotopes[key] ??= new this.Isotope(container, {
              itemSelector,
              sortBy: 'order',
              getSortData: {
                order: function (element) {
                  const item = itemInnerSelector ? element.querySelector(itemInnerSelector) : element;
                  return parseInt(item.dataset.order, 10);
                }
              },
              ...this.isotopeOptions,
            });
            this.isotopeReplace(this.isotopes[key], itemsToAdd, itemsToRemove);
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
        const offset = parseInt(top.getAttribute('data-offset') || 0, 10);
        window.scrollTo({
          top: top.offsetTop - offset,
          behavior: 'smooth'
        });
      }

      /** Update url in browser */
      const jsonUrlParams = new URLSearchParams(url.split('?')[1] || '');
      window.history.pushState({}, '', `${window.location.pathname}?${jsonUrlParams.toString()}`);

      this.afterReplace();
      this.log('Content replacement completed successfully');
      
    } catch (error) {
      this.error('Fetch error during content replacement:', error);
      
      // Fallback: navigate to the URL if AJAX fails
      if (typeof window !== 'undefined') {
        this.log('Falling back to page navigation');
        window.location.href = url;
      }
    }
  }

  replace(container, itemsToAdd, itemsToRemove, itemInnerSelector) {
    /** Remove items */
    itemsToRemove.forEach(item => item.remove());

    /** Insert items based on dataset.order */
    itemsToAdd.forEach(gridItem => {
      const item = itemInnerSelector ? gridItem.querySelector(itemInnerSelector) : gridItem;
      const order = parseInt(item.dataset.order, 10);
      /** get the first item with a higher order */
      const nextItem = [...container.children].find(child => {
        const childItem = itemInnerSelector ? child.querySelector(itemInnerSelector) : child;
        return parseInt(childItem.dataset.order, 10) > order;
      });
      if (nextItem) {
        container.insertBefore(gridItem, nextItem);
      } else {
        container.appendChild(gridItem);
      }
    });
  }

  async loadIsotope() {
    try {
      this.Isotope = (await import('isotope-layout')).default;
      this.log('Isotope library loaded successfully');
    } catch (error) {
      this.error('Failed to load Isotope library', error);
      throw error;
    }
  }

  isotopeReplace(isotope, itemsToAdd, itemsToRemove) {
    if (!isotope) {
      this.error('Isotope instance not provided');
      return;
    }
    
    /** Add and remove items, then layout */
    if (itemsToRemove.length > 0) {
      isotope.remove(itemsToRemove);
    }
    if (itemsToAdd.length > 0) {
      isotope.insert(itemsToAdd);
    }
    // isotope.layout()
  }
}
