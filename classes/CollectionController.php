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
   * Legacy process method - maintained for backwards compatibility
   *
   * @deprecated Use processWithQuery() for new implementations
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

        // Use totalCount if provided, otherwise fall back to collection count
    $actualTotalCount = $totalCount ?? $collection->count();

        // Prepare common data for all snippets
    $baseData = [
      'collection' => $collection,
      'page' => $this->page,
      'config' => $this->config,
      'pagination' => $collection->pagination(),
      'showPagination' => $collection->pagination() ? $collection->pagination()->total() > $collection->pagination()->limit() : false,
      'showIndicator' => true
    ];

        // Prepare specific data only for snippets without controllers
    $snippetData = [
      'items' => array_merge($baseData, [
        'items' => $this->prepareItemsWithIndex($collection),
        'isEmpty' => $actualTotalCount === 0,
        'hasActiveFilters' => $this->hasActiveFilters()
      ])
    ];

    foreach ($this->config['snippets'] as $key => $snippetName) {
      // Skip disabled features
      if ($key === 'search' && !($this->config['enableSearch'] ?? true)) {
        $snippets[$key] = '';
        continue;
      }
      if ($key === 'filters' && !($this->config['enableFilters'] ?? true)) {
        $snippets[$key] = '';
        continue;
      }
      if ($key === 'indicator' && !($this->config['enableIndicator'] ?? true)) {
        $snippets[$key] = '';
        continue;
      }
      if ($key === 'pagination' && !($this->config['enablePagination'] ?? true)) {
        $snippets[$key] = '';
        continue;
      }

      // Items snippet doesn't have a controller, use prepared data
      // All other snippets have controllers and use base data
      $data = $key === 'items' ? $snippetData[$key] : $baseData;

      $snippets[$key] = snippet($snippetName, $data, true);
    }

    return $snippets;
  }

  protected function prepareItemsWithIndex($collection)
  {
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
    // Check for htmx request
    if (get('htmx') || isset($_SERVER['HTTP_HX_REQUEST'])) {
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
      if ($key !== $paginationParam && $key !== 'json' && $value) {
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
