<?php

namespace KirbyCollectionManager;

use KirbyCollectionManager\Config\CollectionConfig;
use KirbyCollectionManager\Config\PaginationConfig;
use KirbyCollectionManager\Config\SearchConfig;
use KirbyCollectionManager\Config\FilterConfig;
use KirbyCollectionManager\Exception\CollectionException;
use KirbyCollectionManager\Exception\InvalidConfigurationException;
use KirbyCollectionManager\Exception\CollectionNotFoundException;
use KirbyCollectionManager\Query\CollectionQuery;
use KirbyCollectionManager\Url\UrlBuilder;
use KirbyCollectionManager\Response\ResponseFactory;
use KirbyCollectionManager\Response\RequestDetector;
use KirbyCollectionManager\Render\SnippetRenderer;

class CollectionController
{
  protected $config;
  protected $page;
  protected $site;

  /**
   * Validated configuration object (optional, for new usage pattern)
   */
  protected ?CollectionConfig $validatedConfig = null;

  /**
   * URL builder instance
   */
  protected ?UrlBuilder $urlBuilder = null;

  /**
   * Collection query instance
   */
  protected ?CollectionQuery $query = null;

  public function __construct($page, $site, $config = [])
  {
    $this->page = $page;
    $this->site = $site;

        // Get default configuration
    $defaultConfig = $this->getDefaultConfig();

        // Handle snippets merging specially
    if (isset($config['snippets'])) {
      $config['snippets'] = array_merge(
          $defaultConfig['snippets'],
          $config['snippets']
      );
    }

    $this->config = array_merge($defaultConfig, $config);

    $paginationParam = (string)($this->config['pagination']['param'] ?? 'p');
    $this->config['instanceId'] = 'cm-' . preg_replace('/[^a-zA-Z0-9_-]/', '', $paginationParam);
  }

  protected function getDefaultConfig()
  {
    return [
      'collection' => 'children',
      'collectionMethod' => 'listed',
      'search' => [
        'fields' => ['title', 'text'],
        'placeholder' => 'Search...',
        'param' => 'q'
      ],
      'pagination' => [
        'limit' => 10,
        'param' => 'p',
        'range' => 5
      ],
      'sorting' => [
        'default' => 'date',
        'direction' => 'desc'
      ],
      'taxonomies' => [],
      'enableSearch' => true,
      'enableFilters' => true,
      'enableIndicator' => true,
      'enablePagination' => true,
      'enableJs' => true,
      'containers' => [
        'wrapper' => '.collection-manager',
        'items' => '.collection-items',
        'pagination' => '.collection-pagination',
        'filters' => '.collection-filters',
        'search' => '.collection-search',
        'indicator' => '.current-page-indicator'
      ],
      'snippets' => [
        'wrapper' => 'collection-manager',
        'items' => 'collection-items',
        'item' => 'collection-item',
        'pagination' => 'collection-pagination',
        'filters' => 'collection-filters',
        'search' => 'collection-search',
        'indicator' => 'current-page-indicator'
      ],
      'replacements' => [
        [
          'containerSelector' => '.collection-items__list',
          'itemSelector' => '.collection-item',
          'snippet' => 'items'
        ],
        [
          'containerSelector' => '.collection-search',
          'outerHTML' => true,
          'snippet' => 'search'
        ],
        [
          'containerSelector' => '.collection-pagination',
          'outerHTML' => true,
          'snippet' => 'pagination'
        ],
        [
          'containerSelector' => '.collection-filters',
          'outerHTML' => true,
          'snippet' => 'filters'
        ],
        [
          'containerSelector' => '.current-page-indicator',
          'outerHTML' => true,
          'snippet' => 'indicator'
        ]
      ]
    ];
  }

    /**
     * Static factory method to create and handle a collection request
     */
  public static function handle($page, $config = [])
  {
    $site = site();
    $controller = new static($page, $site, $config);
    return $controller->process();
  }

  /**
   * Get the URL builder instance
   */
  public function getUrlBuilder(): UrlBuilder
  {
    if ($this->urlBuilder === null) {
      $this->urlBuilder = new UrlBuilder(
        $this->page,
        $this->config['pagination']['param'] ?? 'p',
        $this->config['search']['param'] ?? 'q'
      );
    }
    return $this->urlBuilder;
  }

  /**
   * Process using the new Query Pipeline architecture
   *
   * This method uses the new CollectionQuery, UrlBuilder, and ResponseFactory
   * classes for improved maintainability and testability.
   *
   * @return array Template data
   */
  public function processWithQuery(): array
  {
    // Build query pipeline
    $this->query = $this->buildQueryPipeline();

    // Get the processed collection
    $collection = $this->query->get();

    // Create renderer with URL builder
    $renderer = new SnippetRenderer(
      $this->config,
      $this->page,
      $this->getUrlBuilder()
    );

    // Generate snippets
    $snippets = $renderer->renderAll($collection, $this->query);

    // Handle AJAX requests using ResponseFactory
    if (ResponseFactory::isAjaxRequest()) {
      ResponseFactory::handleIfAjax($collection, $snippets, $this->config);
      // Won't reach here - handlers call exit
    }

    // Return template data
    return $this->buildTemplateData($collection, $snippets);
  }

  /**
   * Build the collection query pipeline
   */
  protected function buildQueryPipeline(): CollectionQuery
  {
    $baseCollection = $this->getBaseCollection();
    $query = CollectionQuery::from($baseCollection);

    // Apply search
    $searchParam = $this->config['search']['param'] ?? 'q';
    if ($this->config['enableSearch'] ?? true) {
      $searchTerm = get($searchParam);
      if ($searchTerm) {
        $query->search($searchTerm, $this->config['search']['fields'] ?? ['title', 'text']);
      }
    }

    // Apply taxonomy filters
    if ($this->config['enableFilters'] ?? true) {
      foreach ($this->config['taxonomies'] ?? [] as $taxonomy) {
        $value = get($taxonomy['param']) ?? param($taxonomy['param']);
        if ($value) {
          $query->filter($taxonomy['field'], $value);
        }
      }
    }

    // Sort
    $sorting = $this->config['sorting'] ?? [];
    $query->sort(
      $sorting['default'] ?? 'date',
      $sorting['direction'] ?? 'desc'
    );

    // Paginate
    if ($this->config['enablePagination'] ?? true) {
      $pagination = $this->config['pagination'] ?? [];
      $query->paginate(
        $pagination['limit'] ?? 10,
        $pagination['param'] ?? 'p'
      );
    }

    return $query;
  }

  /**
   * Build the template data array
   */
  protected function buildTemplateData($collection, array $snippets): array
  {
    $returnData = [
      'collection' => $collection,
      'config' => $this->config,
      'snippets' => $snippets,
      'query' => $this->query,
      'urlBuilder' => $this->getUrlBuilder(),
      'hasActiveFilters' => $this->query?->hasActiveFiltersOrSearch() ?? $this->hasActiveFilters(),
    ];

    // Add pagination info
    if ($this->config['enablePagination'] ?? true) {
      $pagination = $collection->pagination();
      if ($pagination) {
        $returnData['currentPage'] = $pagination->page();
        $returnData['totalPages'] = $pagination->pages();
      }
    }

    return array_merge($snippets, $returnData);
  }

  /**
   * Active processing path, used by CollectionController::handle().
   *
   * Note: processWithQuery() is an unfinished alternative pipeline that is not
   * called anywhere yet and does NOT scope htmx requests per instance; port the
   * instanceId handling from this method before switching over.
   */
  public function process()
  {
        // Get base collection
    $collection = $this->getBaseCollection();

        // Apply search
    $searchParam = $this->config['search']['param'] ?? 'q';
    if ($this->config['enableSearch'] && ($search = get($searchParam))) {
      $searchFields = $this->config['search']['fields'] ?? ['title', 'text'];
      $searchString = is_array($searchFields) ? implode('|', $searchFields) : $searchFields;
      $collection = $collection->search($search, $searchString);
    }

        // Apply taxonomy filters
    if ($this->config['enableFilters']) {
      $collection = $this->applyTaxonomyFilters($collection);
    }

        // Sort collection
    $collection = $this->sortCollection($collection);

        // Store the total count before pagination for empty result handling
    $totalCount = $collection->count();

        // Paginate the collection first
    $paginatedCollection = $this->paginateCollection($collection);

        // Generate HTML snippets
    $snippets = $this->generateSnippets($paginatedCollection, $totalCount);

        // Handle AJAX requests (htmx or legacy JSON)
    $ajaxType = $this->isAjaxRequest();
    if ($ajaxType) {
      return $this->handleAjaxRequest($paginatedCollection, $snippets);
    }

        // Return template data
    $returnData = [
      'collection' => $paginatedCollection,
      'config' => $this->config,
      'snippets' => $snippets
    ];

    // Add currentPage only if pagination is enabled
    if ($this->config['enablePagination'] ?? true) {
      $returnData['currentPage'] = $paginatedCollection->pagination()->page();
    }

    return array_merge($snippets, $returnData);
  }

  protected function getBaseCollection()
  {
    $collection = $this->config['collection'];

        // If collection is already a Collection object, return it directly
    if (is_object($collection) && method_exists($collection, 'count')) {
      return $collection;
    }

        // If collection is a string path, resolve it
    $method = $this->config['collectionMethod'];

    if ($collection === 'children') {
      return $this->page->children()->$method();
    }

        // Support for custom collection paths
    return $this->page->find($collection)->children()->$method();
  }

  protected function applyTaxonomyFilters($collection)
  {
    foreach ($this->config['taxonomies'] as $taxonomy) {
      $param = get($taxonomy['param']) ?? param($taxonomy['param']);
      if ($param) {
        $collection = $collection->filterBy($taxonomy['field'], $param);
      }
    }
    return $collection;
  }

  protected function sortCollection($collection)
  {
    $sorting = $this->config['sorting'] ?? [];
    $field = $sorting['default'] ?? 'date';
    $direction = $sorting['direction'] ?? 'desc';

    return $collection->sortBy($field, $direction);
  }

  protected function addOrderIndices($collection)
  {
        // Create indexed collection for proper ordering
    $indexed = [];
    $index = 0;

    foreach ($collection as $item) {
      $indexed[] = (object) [
        'page' => $item,
        'orderIndex' => $index++
      ];
    }

    return $indexed;
  }

  protected function paginateCollection($collection)
  {
    // If pagination is disabled, return the collection as-is
    if (!($this->config['enablePagination'] ?? true)) {
      return $collection;
    }

    $pagination = $this->config['pagination'] ?? [];

    return $collection->paginate([
      'limit' => $pagination['limit'] ?? 10,
      'method' => 'query',
      'variable' => $pagination['param'] ?? 'p'
    ]);
  }

  protected function generateSnippets($collection, $totalCount = null)
  {
    $snippets = [];
    $pagination = $collection->pagination();
    $actualTotalCount = $totalCount ?? $collection->count();

    // Config params
    $paginationParam = $this->config['pagination']['param'] ?? 'p';
    $searchParam = $this->config['search']['param'] ?? 'q';
    $currentSearch = get($searchParam, '');

    // ========== SEARCH DATA ==========
    $searchData = [
      'page' => $this->page,
      'config' => $this->config,
      'searchParam' => $searchParam,
      'currentSearch' => $currentSearch,
      'hasSearch' => !empty($currentSearch),
      'placeholder' => $this->config['search']['placeholder'] ?? t('collection.search.placeholder', 'Search...'),
      'clearUrl' => self::buildUrl($this->page, [$searchParam => ''], $paginationParam, $searchParam),
    ];

    // ========== FILTERS DATA ==========
    $taxonomies = $this->config['taxonomies'] ?? [];
    $processedTaxonomies = [];

    foreach ($taxonomies as $taxonomy) {
      $param = $taxonomy['param'];
      $field = $taxonomy['field'];
      $label = $taxonomy['label'] ?? ucfirst($param);
      $currentValue = get($param);

      // Get unique values
      $allItems = $this->page->children()->listed();
      $values = $allItems->pluck($field, ',', true);

      if (empty($values)) continue;

      $filterOptions = [];
      foreach ($values as $value) {
        if (empty(trim($value))) continue;
        $filterOptions[] = [
          'value' => $value,
          'label' => $value,
          'isActive' => $currentValue === $value,
          'url' => self::buildUrl($this->page, [$param => $value], $paginationParam, $searchParam),
          'param' => $param
        ];
      }

      $processedTaxonomies[] = [
        'param' => $param,
        'field' => $field,
        'label' => $label,
        'currentValue' => $currentValue,
        'allUrl' => self::buildUrl($this->page, [$param => null], $paginationParam, $searchParam),
        'hasActiveFilter' => !empty($currentValue),
        'options' => $filterOptions
      ];
    }

    $filtersData = [
      'page' => $this->page,
      'config' => $this->config,
      'collection' => $collection,
      'taxonomies' => $processedTaxonomies,
      'hasActiveFilters' => $this->hasActiveFilters(),
      'clearAllUrl' => $this->page->url(),
    ];

    // ========== PAGINATION DATA ==========
    $showPagination = $pagination ? $pagination->total() > $pagination->limit() : false;
    $shouldShowPagination = $showPagination && $pagination && !($pagination->limit() > 0 && $pagination->total() === 0);

    $cssClasses = [
      'nav' => 'collection-pagination',
      'item' => 'collection-pagination__item',
      'icon' => 'collection-pagination__icon'
    ];

    $paginationData = [
      'page' => $this->page,
      'config' => $this->config,
      'pagination' => $pagination,
      'showPagination' => $showPagination,
      'shouldShowPagination' => $shouldShowPagination,
      'cssClasses' => $cssClasses,
    ];

    if ($pagination && $shouldShowPagination) {
      $hasPrevPage = $pagination->hasPrevPage();
      $hasNextPage = $pagination->hasNextPage();
      $currentPage = $pagination->page();
      $range = $this->config['pagination']['range'] ?? 5;
      $rangePages = $pagination->range($range);

      $paginationData = array_merge($paginationData, [
        'hasPrevPage' => $hasPrevPage,
        'hasNextPage' => $hasNextPage,
        'currentPage' => $currentPage,
        'totalPages' => $pagination->pages(),
        'rangePages' => $rangePages,
        'firstPageUrl' => !$hasPrevPage ? '#' : self::buildUrl($this->page, [$paginationParam => null], $paginationParam),
        'prevPageUrl' => !$hasPrevPage ? '#' : self::buildUrl($this->page, [$paginationParam => $pagination->prevPage() > 1 ? $pagination->prevPage() : null], $paginationParam),
        'nextPageUrl' => !$hasNextPage ? '#' : self::buildUrl($this->page, [$paginationParam => $pagination->nextPage()], $paginationParam),
        'lastPageUrl' => !$hasNextPage ? '#' : self::buildUrl($this->page, [$paginationParam => $pagination->lastPage()], $paginationParam),
        'pageUrls' => array_combine($rangePages, array_map(fn($p) => self::buildUrl($this->page, [$paginationParam => $p > 1 ? $p : null], $paginationParam), $rangePages)),
        'firstPageLabel' => t('collection.pagination.first', 'Go to first page'),
        'prevPageLabel' => t('collection.pagination.prev', 'Go to previous page'),
        'nextPageLabel' => t('collection.pagination.next', 'Go to next page'),
        'lastPageLabel' => t('collection.pagination.last', 'Go to last page'),
        'firstPageClasses' => $cssClasses['item'] . ' ' . $cssClasses['item'] . '--to-first' . (!$hasPrevPage ? ' ' . $cssClasses['item'] . '--disabled' : ''),
        'prevPageClasses' => $cssClasses['item'] . ' ' . $cssClasses['item'] . '--to-sibling' . (!$hasPrevPage ? ' ' . $cssClasses['item'] . '--disabled' : ''),
        'nextPageClasses' => $cssClasses['item'] . ' ' . $cssClasses['item'] . '--to-sibling' . (!$hasNextPage ? ' ' . $cssClasses['item'] . '--disabled' : ''),
        'lastPageClasses' => $cssClasses['item'] . ' ' . $cssClasses['item'] . '--to-last' . (!$hasNextPage ? ' ' . $cssClasses['item'] . '--disabled' : ''),
        'paginationParam' => $paginationParam,
      ]);
    }

    // ========== INDICATOR DATA ==========
    $shouldRenderIndicator = $pagination && $pagination->pages() > 1 && $pagination->total() > 0;
    $indicatorFormat = t('collection.pagination.indicator', 'Page {current} of {total}');

    $indicatorData = [
      'config' => $this->config,
      'pagination' => $pagination,
      'shouldRender' => $shouldRenderIndicator,
      'indicatorText' => $shouldRenderIndicator ? str_replace(['{current}', '{total}'], [$pagination->page(), $pagination->pages()], $indicatorFormat) : '',
      'currentPage' => $pagination ? $pagination->page() : 1,
      'totalPages' => $pagination ? $pagination->pages() : 1,
    ];

    // ========== ITEMS DATA ==========
    $itemsData = [
      'page' => $this->page,
      'config' => $this->config,
      'collection' => $collection,
      'items' => $this->prepareItemsWithIndex($collection),
      'isEmpty' => $actualTotalCount === 0,
      'hasActiveFilters' => $this->hasActiveFilters(),
    ];

    // ========== RENDER SNIPPETS ==========
    $snippetDataMap = [
      'search' => $searchData,
      'filters' => $filtersData,
      'pagination' => $paginationData,
      'indicator' => $indicatorData,
      'items' => $itemsData,
    ];

    foreach ($this->config['snippets'] as $key => $snippetName) {
      // Skip disabled features
      $featureFlags = [
        'search' => 'enableSearch',
        'filters' => 'enableFilters',
        'indicator' => 'enableIndicator',
        'pagination' => 'enablePagination',
      ];

      if (isset($featureFlags[$key]) && !($this->config[$featureFlags[$key]] ?? true)) {
        $snippets[$key] = '';
        continue;
      }

      $data = $snippetDataMap[$key] ?? ['config' => $this->config, 'page' => $this->page];
      $snippets[$key] = snippet($snippetName, $data, true);
    }

    return $snippets;
  }

  protected function prepareItemsWithIndex($collection)
  {
    // Return pages directly as an array of Page objects
    return $collection->values();
  }

  protected function getActiveFilters()
  {
    $filters = [];
    foreach ($this->config['taxonomies'] as $taxonomy) {
      $value = get($taxonomy['param']) ?? param($taxonomy['param']);
      if ($value) {
        $filters[$taxonomy['param']] = [
          'label' => $taxonomy['label'],
          'value' => $value,
          'clearUrl' => self::buildUrl(
              $this->page,
              [$taxonomy['param'] => null],
              $this->config['pagination']['param'] ?? 'p',
              $this->config['search']['param'] ?? 'q'
          )
        ];
      }
    }
    return $filters;
  }

  protected function hasActiveFilters()
  {
    $searchParam = $this->config['search']['param'] ?? 'q';
    $search = get($searchParam);
    if ($search) {
      return true;
    }

    foreach ($this->config['taxonomies'] as $taxonomy) {
      if (get($taxonomy['param']) ?? param($taxonomy['param'])) {
        return true;
      }
    }
    return false;
  }

  protected function isAjaxRequest()
  {
    // Check for htmx request, scoped to this instance so that several
    // collection managers can live on the same page
    $htmxParam = get('htmx');
    if ($htmxParam) {
      return $htmxParam === ($this->config['instanceId'] ?? '') ? 'htmx' : false;
    }
    if (isset($_SERVER['HTTP_HX_REQUEST'])) {
      return 'htmx';
    }

    // Check for legacy JSON request
    if (get('json') && (
          ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest' ||
          strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false
      )) {
      return 'json';
    }

    return false;
  }

  protected function handleAjaxRequest($collection, $snippets)
  {
    $requestType = $this->isAjaxRequest();

    // Handle htmx requests - return HTML fragment
    if ($requestType === 'htmx') {
      return $this->handleHtmxRequest($collection, $snippets);
    }

    // Handle legacy JSON requests
    return $this->handleJsonRequest($collection, $snippets);
  }

  protected function handleHtmxRequest($collection, $snippets)
  {
    while (ob_get_level() > 0) {
      ob_end_clean();
    }

    header('Content-Type: text/html; charset=utf-8');

    // Return the HTML content that htmx will swap
    $html = '';

    // Search section
    if ($this->config['enableSearch'] ?? true) {
      $html .= '<div class="' . trim($this->config['containers']['search'] ?? '', '.') . '">';
      $html .= $snippets['search'] ?? '';
      $html .= '</div>';
    }

    // Filters section
    if ($this->config['enableFilters'] ?? true) {
      $html .= '<div class="' . trim($this->config['containers']['filters'] ?? '', '.') . '">';
      $html .= $snippets['filters'] ?? '';
      $html .= '</div>';
    }

    // Items section
    $html .= '<div class="' . trim($this->config['containers']['items'] ?? '', '.') . '" data-replacementtop="true" data-offset="100">';
    $html .= $snippets['items'] ?? '';
    $html .= '</div>';

    // Pagination and indicator
    $html .= '<div class="collection-pagination-wrapper">';
    $html .= $snippets['pagination'] ?? '';
    $html .= $snippets['indicator'] ?? '';
    $html .= '</div>';

    echo $html;
    exit;
  }

  protected function handleJsonRequest($collection, $snippets)
  {
    header('Content-Type: application/json');

    $replacements = [];

    foreach ($this->config['replacements'] as $replacement) {
      $snippetKey = $replacement['snippet'];
      if (isset($snippets[$snippetKey])) {
        $replacements[] = array_merge($replacement, [
          'data' => $snippets[$snippetKey]
        ]);
      }
    }

    echo json_encode([
      'collection' => $collection,
      'snippets' => $snippets,
      'replacements' => $replacements
    ]);
    exit;
  }

    /**
     * Helper method to build URLs with preserved parameters
     */
  public static function buildUrl($page, $params = [], $paginationParam = null, $searchParam = null)
  {
    // Use defaults if not provided
    $paginationParam = $paginationParam ?? 'p';
    $searchParam = $searchParam ?? 'q';

    $currentParams = [];

        // Preserve search query
    if ($search = get($searchParam)) {
      $currentParams[$searchParam] = $search;
    }

        // Preserve taxonomy filters - get all current GET parameters except pagination
    foreach ($_GET as $key => $value) {
      if ($key !== $paginationParam && $key !== 'json' && $key !== 'htmx' && $value) {
        $currentParams[$key] = $value;
      }
    }

        // Merge with new parameters (new ones override current ones)
    $allParams = array_merge($currentParams, $params);

        // Remove empty/null parameters
    $allParams = array_filter($allParams, function ($value) {
        return $value !== null && $value !== '';
    });

    $url = $page->url();
    if (!empty($allParams)) {
      $url .= '?' . http_build_query($allParams);
    }

    return $url;
  }

  /**
   * Get validated configuration object
   *
   * Creates a CollectionConfig DTO from the current config array.
   * This provides type-safe access to configuration with validation.
   *
   * @return CollectionConfig
   * @throws InvalidConfigurationException if configuration is invalid
   */
  public function getValidatedConfig(): CollectionConfig
  {
    if ($this->validatedConfig === null) {
      $this->validatedConfig = CollectionConfig::fromArray([
        'pagination' => $this->config['pagination'] ?? [],
        'search' => $this->config['search'] ?? [],
        'filter' => ['taxonomies' => $this->config['taxonomies'] ?? []],
        'enableJs' => $this->config['enableJs'] ?? true,
        'snippets' => $this->config['snippets'] ?? [],
        'sortBy' => $this->config['sorting']['default'] ?? null,
        'sortDirection' => $this->config['sorting']['direction'] ?? 'desc',
      ]);
    }

    return $this->validatedConfig;
  }

  /**
   * Validate that the collection source is valid
   *
   * @param mixed $collection The collection to validate
   * @return void
   * @throws CollectionNotFoundException if collection is invalid
   */
  protected function validateCollectionSource($collection): void
  {
    if ($collection === null) {
      throw CollectionNotFoundException::undefinedSource(
        is_string($this->config['collection'])
          ? $this->config['collection']
          : 'closure'
      );
    }

    if (!is_object($collection) || !method_exists($collection, 'count')) {
      throw CollectionNotFoundException::invalidType(
        'collection',
        is_object($collection) ? get_class($collection) : gettype($collection)
      );
    }
  }

  /**
   * Get the current page
   */
  public function getPage()
  {
    return $this->page;
  }

  /**
   * Get the raw config array
   */
  public function getConfig(): array
  {
    return $this->config;
  }
}
