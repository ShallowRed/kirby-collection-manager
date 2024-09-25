export class CollectionManager {

  route = null;
  isotopes = {};

  constructor({
    contentRoute,
    useIsotope,
    isotopeOptions,
    afterReplace = () => { }
  }) {
    this.route = contentRoute
    this.useIsotope = useIsotope
    this.isotopeOptions = isotopeOptions
    this.afterReplace = afterReplace
  }

  get baseUrl() {
    return `${window.location.origin}${this.route}`
  }

  listenPaginationEvent = function (paginationLink) {
    paginationLink.addEventListener('click', async (event) => {
      event.preventDefault()
      const link = event.target.closest('a')
      if (!link.hasAttribute('data-page')) {
        return
      }
      const pageNumber = link.getAttribute('data-page')
      this.paginate(pageNumber)
    })
  }
  paginate(pageNumber) {
    document.querySelectorAll('[data-first-page-only]')
      .forEach(element => {
        element.style.display = pageNumber === '1' ? 'block' : 'none'
      })
    const currentUrlParams = new URLSearchParams(window.location.search)
    currentUrlParams.set('p', pageNumber)
    this.replaceContent(`${this.baseUrl}?${currentUrlParams.toString()}`)
  }

  listenTaxonomyEvent(taxonomyLink) {
    taxonomyLink.addEventListener('click', async (event) => {
      event.preventDefault()
      const link = event.target.closest('a')
      if (!link.hasAttribute('data-param') || !link.hasAttribute('data-value')) {
        return
      }
      const param = link.getAttribute('data-param')
      const value = link.getAttribute('data-value')
      this.toggleParam(param, value)
    })
  }
  toggleParam(param, value) {
    const currentUrlParams = new URLSearchParams(window.location.search)
    if (currentUrlParams.get(param) === value) {
      currentUrlParams.delete(param)
      document.querySelectorAll(`[data-first-page-only]`)
        .forEach(element => {
          element.style.display = 'block'
        })
    } else {
      currentUrlParams.set(param, value)
      document.querySelectorAll(`[data-first-page-only]`)
        .forEach(element => {
          element.style.display = 'none'
        })
    }
    if (currentUrlParams.get('p')) {
      /** Remove pagination */
      currentUrlParams.delete('p')
    }
    const url = `${this.baseUrl}?${currentUrlParams.toString()}`
    this.replaceContent(url)
  }

  listenSearchEvent(form) {
    const submit = form.querySelector('input[type="submit"]')
    submit?.addEventListener('click', async (event) => {
      event.preventDefault()
      const search = form.querySelector('input[type="search"]')
      const queryParam = search.getAttribute('name')
      if (queryParam && search?.value) {
        this.search(queryParam, search.value)
      }
    })
  }
  search(queryParam, query) {
    const currentUrlParams = new URLSearchParams(window.location.search)
    currentUrlParams.set(queryParam, query)
    if (currentUrlParams.get('p')) {
      /** Remove pagination */
      currentUrlParams.delete('p')
    }
    const url = `${this.baseUrl}?${currentUrlParams.toString()}`
    this.replaceContent(url)
  }

  async replaceContent(url) {
    // console.log('fetch url:', url)
    try {

      /** Fetch response */
      const response = await fetch(url)
      const data = await response.json()

      /** Replace content */
      data.replacements?.forEach(replacement => {

        const container = document.querySelector(replacement.containerSelector)
        if (!container) {
          return
        }
        if (replacement.outerHTML === true) {
          container.outerHTML = replacement.data
          return
        }
        const itemSelector = replacement.itemSelector
        const itemInnerSelector = replacement.itemInnerSelector ?? null

        /** Parse new items */
        const parser = new DOMParser();
        const doc = parser.parseFromString(replacement.data, 'text/html');

        /** Compute items to add and remove */
        const itemsToAdd = [...doc.querySelectorAll(itemSelector)]
          .filter(gridItem => {
            const item = itemInnerSelector ? gridItem.querySelector(itemInnerSelector) : gridItem
            const id = item?.dataset.id
            return !container.querySelector(`[data-id="${id}"]`)
          })
        const itemsToRemove = [...container.querySelectorAll(itemSelector)]
          .filter(gridItem => {
            const item = itemInnerSelector ? gridItem.querySelector(itemInnerSelector) : gridItem
            const id = item?.dataset.id
            return !doc.querySelector(`[data-id="${id}"]`)
          })

        if (this.useIsotope && replacement.isotope) {
          /** Initialize isotope if not already done */
          const key = replacement.containerSelector /** This could be improved, if multiple isotope grids are used  with the same containerSelector */
          this.isotopes[key] ??= new this.Isotope(container, {
            itemSelector,
            sortBy: 'order',
            getSortData: {
              order: function (element) {
                const item = itemInnerSelector ? element.querySelector(itemInnerSelector) : element
                return parseInt(item.dataset.order, 10)
              }
            },
            ...this.isotopeOptions,
          })
          this.isotopeReplace(this.isotopes[key], itemsToAdd, itemsToRemove)
        }
        else {
          this.replace(container, itemsToAdd, itemsToRemove, itemInnerSelector)
        }
      })

      /** Eventually scroll */
      const top = document.querySelector("[data-replacementtop='true']")
      if (top) {
        const offset = top.getAttribute('data-offset') || 0
        window.scrollTo({
          top: top.offsetTop - offset,
          behavior: 'smooth'
        })
      }

      /** Update url in browser */
      const jsonUrlParams = new URLSearchParams(url.split('?')[1])
      window.history.pushState({}, '', `${window.location.pathname}?${jsonUrlParams.toString()}`)

      this.afterReplace()
    }
    catch (error) {
      console.log('Fetch error: ', error)
    }
  }

  replace(container, itemsToAdd, itemsToRemove, itemInnerSelector) {
    /** Remove items */
    itemsToRemove.forEach(item => item.remove())

    /** Insert items based on dataset.order */
    itemsToAdd.forEach(gridItem => {
      const item = itemInnerSelector ? gridItem.querySelector(itemInnerSelector) : gridItem
      const order = parseInt(item.dataset.order, 10)
      /** get the first item with a higher order */
      const nextItem = [...container.children].find(child => {
        const childItem = itemInnerSelector ? child.querySelector(itemInnerSelector) : child
        return parseInt(childItem.dataset.order, 10) > order
      })
      if (nextItem) {
        container.insertBefore(gridItem, nextItem)
      } else {
        container.appendChild(gridItem)
      }
    })
  }

  async loadIsotope() {
    this.Isotope = (await import('isotope-layout')).default
  }
  isotopeReplace(isotope, itemsToAdd, itemsToRemove) {
    /** Add and remove items, then layout */
    isotope.remove(itemsToRemove)
    isotope.insert(itemsToAdd)
    // isotope.layout()
  }
}
