<?php

namespace KirbyCollectionManager;

class CollectionController
{
    protected $config;
    protected $page;
    protected $site;

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
                'placeholder' => 'Search...'
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

    public function process()
    {
        // Get base collection
        $collection = $this->getBaseCollection();

        // Apply search
        if ($this->config['enableSearch'] && ($search = get('q'))) {
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

        // Handle AJAX requests
        if ($this->isAjaxRequest()) {
            return $this->handleAjaxRequest($paginatedCollection, $snippets);
        }

        // Return template data
        return array_merge($snippets, [
            'collection' => $paginatedCollection,
            'config' => $this->config,
            'currentPage' => $paginatedCollection->pagination()->page(),
            'snippets' => $snippets
        ]);
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
            'config' => $this->config
        ];

        // Prepare specific data for each snippet type
        $snippetData = [
            'search' => array_merge($baseData, [
                'currentSearch' => get('q', ''),
                'hasSearch' => !empty(get('q', '')),
                'placeholder' => $this->config['search']['placeholder'] ?? 'Search...',
                'clearUrl' => self::buildUrl($this->page, ['q' => null])
            ]),
            'filters' => array_merge($baseData, [
                'taxonomies' => $this->config['taxonomies'] ?? [],
                'activeFilters' => $this->getActiveFilters()
            ]),
            'items' => array_merge($baseData, [
                'items' => $this->prepareItemsWithIndex($collection),
                'isEmpty' => $actualTotalCount === 0,
                'hasActiveFilters' => $this->hasActiveFilters()
            ]),
            'pagination' => array_merge($baseData, [
                'range' => $this->config['pagination']['range'] ?? 5,
                'pagination' => $collection->pagination(),
                'showPagination' => $collection->pagination()->pages() > 1 && $actualTotalCount > 0
            ]),
            'indicator' => array_merge($baseData, [
                'pagination' => $collection->pagination(),
                'format' => $this->config['texts']['pageIndicatorShort'] ?? 'Page {current} of {total}',
                'showIndicator' => $actualTotalCount > 0
            ])
        ];

        foreach ($this->config['snippets'] as $key => $snippetName) {
            $data = $snippetData[$key] ?? $baseData;
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
                    'clearUrl' => self::buildUrl($this->page, [$taxonomy['param'] => null])
                ];
            }
        }
        return $filters;
    }

    protected function hasActiveFilters()
    {
        $search = get('q');
        if ($search) return true;

        foreach ($this->config['taxonomies'] as $taxonomy) {
            if (get($taxonomy['param']) ?? param($taxonomy['param'])) {
                return true;
            }
        }
        return false;
    }

    protected function isAjaxRequest()
    {
        return get('json') && (
            ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest' ||
            strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false
        );
    }

    protected function handleAjaxRequest($collection, $snippets)
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
    public static function buildUrl($page, $params = [], $paginationParam = 'p')
    {
        $currentParams = [];

        // Preserve search query
        if ($search = get('q')) {
            $currentParams['q'] = $search;
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
        $allParams = array_filter($allParams, function($value) {
            return $value !== null && $value !== '';
        });

        $url = $page->url();
        if (!empty($allParams)) {
            $url .= '?' . http_build_query($allParams);
        }

        return $url;
    }
}
