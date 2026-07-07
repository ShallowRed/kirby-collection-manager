<?php

namespace KirbyCollectionManager;

use Kirby\Toolkit\Str;
use KirbyCollectionManager\Query\CollectionQuery;
use KirbyCollectionManager\Url\UrlBuilder;

class CollectionController
{
  public const SEARCH_MAX_LENGTH = 100;

  protected $config;
  protected $page;
  protected $site;

  protected ?UrlBuilder $urlBuilder = null;
  protected ?CollectionQuery $query = null;

  public function __construct($page, $site, $config = [])
  {
    $this->page = $page;
    $this->site = $site;

    $defaultConfig = $this->getDefaultConfig();

    // Merge nested option groups so partial overrides keep the defaults
    foreach (['snippets', 'containers', 'search', 'pagination', 'sorting'] as $group) {
      if (isset($config[$group]) && is_array($config[$group])) {
        $config[$group] = array_merge($defaultConfig[$group], $config[$group]);
      }
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
        'placeholder' => null,
        'param' => 'q'
      ],
      'pagination' => [
        'limit' => 10,
        'param' => 'p',
        'range' => 5
      ],
      'sorting' => [
        'default' => 'date',
        'direction' => 'desc',
        'options' => [],
        'param' => 'sort'
      ],
      'taxonomies' => [],
      'enableSearch' => true,
      'enableFilters' => true,
      'enableSorting' => false,
      'enableIndicator' => true,
      'enablePagination' => true,
      'enableJs' => true,
      'containers' => [
        'wrapper' => '.collection-manager',
        'items' => '.collection-items',
        'pagination' => '.collection-pagination',
        'filters' => '.collection-filters',
        'search' => '.collection-search',
        'sorting' => '.collection-sorting',
        'indicator' => '.current-page-indicator'
      ],
      'snippets' => [
        'wrapper' => 'collection-manager',
        'items' => 'collection-items',
        'item' => 'collection-item',
        'pagination' => 'collection-pagination',
        'filters' => 'collection-filters',
        'search' => 'collection-search',
        'sorting' => 'collection-sorting',
        'indicator' => 'current-page-indicator'
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

  public function process()
  {
    if ($this->config['enableJs'] ?? true) {
      // Fragment and full-page responses share the same URL
      header('Vary: HX-Target');
    }

    $this->query = CollectionQuery::from($this->getBaseCollection());

    if (($this->config['enableSearch'] ?? true) && ($search = $this->currentSearch()) !== '') {
      $this->query->search($search, $this->config['search']['fields'] ?? ['title', 'text']);
    }

    if ($this->config['enableFilters'] ?? true) {
      foreach ($this->config['taxonomies'] ?? [] as $taxonomy) {
        $values = $this->currentFilterValues($taxonomy['param']);
        if ($values !== []) {
          $this->query->filter($taxonomy['field'], $values);
        }
      }
    }

    [$sortField, $sortDirection] = $this->currentSorting();
    $this->query->sort($sortField, $sortDirection);

    if ($this->config['enablePagination'] ?? true) {
      $this->query->paginate(
          (int)($this->config['pagination']['limit'] ?? 10),
          (string)($this->config['pagination']['param'] ?? 'p')
      );
    }

    $collection = $this->query->get();
    $snippets = $this->generateSnippets($collection, $this->query->getTotalCount());

    if ($this->isHtmxRequest()) {
      return $this->renderHtmxFragment($snippets);
    }

    $returnData = [
      'collection' => $collection,
      'config' => $this->config,
      'snippets' => $snippets
    ];

    if (($this->config['enablePagination'] ?? true) && $pagination = $collection->pagination()) {
      $returnData['currentPage'] = $pagination->page();
      $returnData['totalPages'] = $pagination->pages();
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

  /**
   * Current search term, trimmed and capped
   */
  protected function currentSearch(): string
  {
    $searchParam = $this->config['search']['param'] ?? 'q';
    $search = trim((string) get($searchParam, ''));

    return Str::substr($search, 0, static::SEARCH_MAX_LENGTH);
  }

  /**
   * Current values for a filter param (comma-separated for multi-select)
   */
  protected function currentFilterValues(string $param): array
  {
    $raw = (string) (get($param) ?? param($param) ?? '');

    return array_values(array_filter(array_map('trim', explode(',', $raw)), fn ($value) => $value !== ''));
  }

  /**
   * Resolve the active sort field and direction.
   *
   * The sort param is only honored when sorting options are configured and
   * the requested key is part of them (whitelist). Option keys may embed a
   * direction with the "field:direction" syntax.
   */
  protected function currentSorting(): array
  {
    $sorting = $this->config['sorting'] ?? [];
    $key = null;

    $options = $sorting['options'] ?? [];
    if (($this->config['enableSorting'] ?? false) && $options !== []) {
      $requested = (string) get($sorting['param'] ?? 'sort', '');
      if ($requested !== '' && array_key_exists($requested, $options)) {
        $key = $requested;
      }
    }

    $key ??= (string)($sorting['default'] ?? 'date');

    $field = $key;
    $direction = strtolower((string)($sorting['direction'] ?? 'desc'));

    if (str_contains($key, ':')) {
      [$field, $direction] = explode(':', $key, 2);
    }

    if (!in_array($direction, ['asc', 'desc'], true)) {
      $direction = 'desc';
    }

    return [$field, $direction];
  }

  /**
   * The current sort option key when it differs from the default
   */
  protected function currentSortKey(): ?string
  {
    $sorting = $this->config['sorting'] ?? [];
    $options = $sorting['options'] ?? [];

    if (!($this->config['enableSorting'] ?? false) || $options === []) {
      return null;
    }

    $requested = (string) get($sorting['param'] ?? 'sort', '');

    return $requested !== '' && array_key_exists($requested, $options) ? $requested : null;
  }

  /**
   * All params owned by this instance (used to preserve state across links)
   */
  protected function knownParams(): array
  {
    $params = [
      $this->config['pagination']['param'] ?? 'p',
      $this->config['search']['param'] ?? 'q',
      $this->config['sorting']['param'] ?? 'sort',
    ];

    foreach ($this->config['taxonomies'] ?? [] as $taxonomy) {
      $params[] = $taxonomy['param'];
    }

    return array_values(array_unique($params));
  }

  /**
   * Currently active known params as [param => value], minus exclusions
   */
  protected function activeKnownParams(array $except = []): array
  {
    $active = [];

    foreach ($this->knownParams() as $param) {
      if (in_array($param, $except, true)) {
        continue;
      }
      $value = get($param);
      if ($value !== null && $value !== '') {
        $active[$param] = $value;
      }
    }

    return $active;
  }

  public function getUrlBuilder(): UrlBuilder
  {
    if ($this->urlBuilder === null) {
      $this->urlBuilder = new UrlBuilder($this->page, $this->knownParams());
    }

    return $this->urlBuilder;
  }

  protected function generateSnippets($collection, $totalCount = null)
  {
    $snippets = [];
    $urls = $this->getUrlBuilder();
    $pagination = $collection->pagination();
    $actualTotalCount = $totalCount ?? $collection->count();

    $paginationParam = $this->config['pagination']['param'] ?? 'p';
    $searchParam = $this->config['search']['param'] ?? 'q';
    $sortParam = $this->config['sorting']['param'] ?? 'sort';
    $currentSearch = $this->currentSearch();

    // ========== SEARCH DATA ==========
    $searchData = [
      'page' => $this->page,
      'config' => $this->config,
      'searchParam' => $searchParam,
      'currentSearch' => $currentSearch,
      'hasSearch' => $currentSearch !== '',
      'placeholder' => $this->config['search']['placeholder'] ?? t('collection.search.placeholder', 'Search...'),
      'clearUrl' => $urls->build([$searchParam => null, $paginationParam => null]),
      'preservedParams' => $this->activeKnownParams([$searchParam, $paginationParam]),
    ];

    // ========== FILTERS DATA ==========
    $processedTaxonomies = [];

    foreach ($this->config['taxonomies'] ?? [] as $taxonomy) {
      $param = $taxonomy['param'];
      $field = $taxonomy['field'];
      $label = $taxonomy['label'] ?? ucfirst($param);
      $multiple = $taxonomy['multiple'] ?? false;
      $currentValues = $this->currentFilterValues($param);

      // Options reflect the full configured collection, not the current page
      $values = $this->getBaseCollection()->pluck($field, ',', true);

      if (empty($values)) {
        continue;
      }

      $filterOptions = [];
      foreach ($values as $value) {
        $value = trim((string) $value);
        if ($value === '') {
          continue;
        }

        $isActive = in_array($value, $currentValues, true);

        if ($multiple) {
          $nextValues = $isActive
          ? array_values(array_diff($currentValues, [$value]))
          : array_merge($currentValues, [$value]);
          $urlValue = $nextValues === [] ? null : implode(',', $nextValues);
        } else {
          $urlValue = $isActive ? null : $value;
        }

        $filterOptions[] = [
          'value' => $value,
          'label' => $value,
          'isActive' => $isActive,
          'url' => $urls->build([$param => $urlValue, $paginationParam => null]),
          'param' => $param
        ];
      }

      $processedTaxonomies[] = [
        'param' => $param,
        'field' => $field,
        'label' => $label,
        'multiple' => $multiple,
        'currentValue' => $currentValues === [] ? null : implode(',', $currentValues),
        'currentValues' => $currentValues,
        'allUrl' => $urls->build([$param => null, $paginationParam => null]),
        'hasActiveFilter' => $currentValues !== [],
        'options' => $filterOptions
      ];
    }

    $filtersData = [
      'page' => $this->page,
      'config' => $this->config,
      'collection' => $collection,
      'taxonomies' => $processedTaxonomies,
      'hasActiveFilters' => $this->hasActiveFilters(),
      'clearAllUrl' => $urls->build(array_fill_keys($this->knownParams(), null)),
    ];

    // ========== SORTING DATA ==========
    $sortingOptions = $this->config['sorting']['options'] ?? [];
    $sortingData = [
      'page' => $this->page,
      'config' => $this->config,
      'sortParam' => $sortParam,
      'options' => $sortingOptions,
      'currentSort' => $this->currentSortKey() ?? ($this->config['sorting']['default'] ?? null),
      'shouldRender' => ($this->config['enableSorting'] ?? false) && count($sortingOptions) > 1,
      'preservedParams' => $this->activeKnownParams([$sortParam, $paginationParam]),
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
      $disabledSuffix = ' (' . t('collection.pagination.disabled', 'disabled') . ')';

      $paginationData = array_merge($paginationData, [
        'hasPrevPage' => $hasPrevPage,
        'hasNextPage' => $hasNextPage,
        'currentPage' => $currentPage,
        'totalPages' => $pagination->pages(),
        'rangePages' => $rangePages,
        'firstPageUrl' => !$hasPrevPage ? '#' : $urls->build([$paginationParam => null]),
        'prevPageUrl' => !$hasPrevPage ? '#' : $urls->build([$paginationParam => $pagination->prevPage() > 1 ? $pagination->prevPage() : null]),
        'nextPageUrl' => !$hasNextPage ? '#' : $urls->build([$paginationParam => $pagination->nextPage()]),
        'lastPageUrl' => !$hasNextPage ? '#' : $urls->build([$paginationParam => $pagination->lastPage()]),
        'pageUrls' => array_combine($rangePages, array_map(fn ($p) => $urls->build([$paginationParam => $p > 1 ? $p : null]), $rangePages)),
        'firstPageLabel' => t('collection.pagination.first', 'Go to first page') . (!$hasPrevPage ? $disabledSuffix : ''),
        'prevPageLabel' => t('collection.pagination.prev', 'Go to previous page') . (!$hasPrevPage ? $disabledSuffix : ''),
        'nextPageLabel' => t('collection.pagination.next', 'Go to next page') . (!$hasNextPage ? $disabledSuffix : ''),
        'lastPageLabel' => t('collection.pagination.last', 'Go to last page') . (!$hasNextPage ? $disabledSuffix : ''),
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
      'items' => $collection->values(),
      'isEmpty' => $actualTotalCount === 0,
      'hasActiveFilters' => $this->hasActiveFilters(),
    ];

    // ========== RENDER SNIPPETS ==========
    $snippetDataMap = [
      'search' => $searchData,
      'filters' => $filtersData,
      'sorting' => $sortingData,
      'pagination' => $paginationData,
      'indicator' => $indicatorData,
      'items' => $itemsData,
    ];

    $featureFlags = [
      'search' => 'enableSearch',
      'filters' => 'enableFilters',
      'sorting' => 'enableSorting',
      'indicator' => 'enableIndicator',
      'pagination' => 'enablePagination',
    ];

    foreach ($this->config['snippets'] as $key => $snippetName) {
      if ($key === 'wrapper' || $key === 'item') {
        continue;
      }

      if (isset($featureFlags[$key]) && !($this->config[$featureFlags[$key]] ?? true)) {
        $snippets[$key] = '';
        continue;
      }

      $data = $snippetDataMap[$key] ?? ['config' => $this->config, 'page' => $this->page];
      $snippets[$key] = snippet($snippetName, $data, true);
    }

    return $snippets;
  }

  /**
   * Whether search or any configured filter is active
   */
  protected function hasActiveFilters()
  {
    if ($this->currentSearch() !== '') {
      return true;
    }

    foreach ($this->config['taxonomies'] ?? [] as $taxonomy) {
      if ($this->currentFilterValues($taxonomy['param']) !== []) {
        return true;
      }
    }

    return false;
  }

  /**
   * Whether the current request is an htmx fragment request for THIS instance.
   *
   * htmx sends the id of the swap target in the HX-Target header; each
   * instance owns the "<instanceId>-content" element, which makes the check
   * multi-instance safe without polluting URLs with extra params.
   */
  protected function isHtmxRequest(): bool
  {
    $target = $_SERVER['HTTP_HX_TARGET'] ?? null;
    if ($target !== null) {
      return $target === ($this->config['instanceId'] ?? '') . '-content';
    }

    // Legacy support for links generated with an explicit htmx param
    return get('htmx') === ($this->config['instanceId'] ?? '');
  }

  protected function renderHtmxFragment($snippets)
  {
    while (ob_get_level() > 0) {
      ob_end_clean();
    }

    header('Content-Type: text/html; charset=utf-8');
    header('Vary: HX-Target');

    $html = '';

    if ($this->config['enableSearch'] ?? true) {
      $html .= '<div class="' . trim($this->config['containers']['search'] ?? '', '.') . '">';
      $html .= $snippets['search'] ?? '';
      $html .= '</div>';
    }

    if ($this->config['enableFilters'] ?? true) {
      $html .= '<div class="' . trim($this->config['containers']['filters'] ?? '', '.') . '">';
      $html .= $snippets['filters'] ?? '';
      $html .= '</div>';
    }

    if ($this->config['enableSorting'] ?? false) {
      $html .= '<div class="' . trim($this->config['containers']['sorting'] ?? '', '.') . '">';
      $html .= $snippets['sorting'] ?? '';
      $html .= '</div>';
    }

    $html .= '<div class="' . trim($this->config['containers']['items'] ?? '', '.') . '">';
    $html .= $snippets['items'] ?? '';
    $html .= '</div>';

    $html .= '<div class="collection-pagination-wrapper">';
    $html .= $snippets['pagination'] ?? '';
    $html .= $snippets['indicator'] ?? '';
    $html .= '</div>';

    echo $html;
    exit;
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

  /**
   * Get the query used by process(), for debugging
   */
  public function getQuery(): ?CollectionQuery
  {
    return $this->query;
  }
}
