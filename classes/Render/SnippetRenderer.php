<?php

declare(strict_types=1);

namespace KirbyCollectionManager\Render;

use Kirby\Cms\Collection;
use Kirby\Cms\Page;
use KirbyCollectionManager\Query\CollectionQuery;
use KirbyCollectionManager\Url\UrlBuilder;

/**
 * Snippet Renderer for Collection Manager
 *
 * Handles the generation of HTML snippets for each component
 * of the collection manager (items, pagination, search, filters).
 */
final class SnippetRenderer
{
    /**
     * Plugin configuration
     */
    private array $config;

    /**
     * The page context
     */
    private Page $page;

    /**
     * URL builder instance
     */
    private UrlBuilder $urlBuilder;

    /**
     * Create a new snippet renderer
     */
    public function __construct(array $config, Page $page, UrlBuilder $urlBuilder)
    {
        $this->config = $config;
        $this->page = $page;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Create a renderer instance
     */
    public static function create(array $config, Page $page, UrlBuilder $urlBuilder): self
    {
        return new self($config, $page, $urlBuilder);
    }

    /**
     * Render all snippets for the collection
     *
     * @param Collection $collection The paginated collection
     * @param CollectionQuery|null $query Optional query for additional info
     * @return array Associative array of snippet HTML keyed by component name
     */
    public function renderAll(Collection $collection, ?CollectionQuery $query = null): array
    {
        $snippets = [];

        // Prepare common data for all snippets
        $baseData = $this->buildBaseData($collection, $query);

        // Render each snippet based on config
        foreach ($this->config['snippets'] as $key => $snippetName) {
            // Skip disabled features
            if (!$this->isFeatureEnabled($key)) {
                $snippets[$key] = '';
                continue;
            }

            // Build specific data for this snippet type
            $data = $this->buildSnippetData($key, $baseData, $collection, $query);

            // Render the snippet
            $snippets[$key] = snippet($snippetName, $data, true);
        }

        return $snippets;
    }

    /**
     * Render a specific snippet
     */
    public function render(
        string $key,
        Collection $collection,
        ?CollectionQuery $query = null
    ): string {
        if (!$this->isFeatureEnabled($key)) {
            return '';
        }

        $snippetName = $this->config['snippets'][$key] ?? "collection-{$key}";
        $baseData = $this->buildBaseData($collection, $query);
        $data = $this->buildSnippetData($key, $baseData, $collection, $query);

        return snippet($snippetName, $data, true);
    }

    /**
     * Build the base data shared by all snippets
     */
    private function buildBaseData(Collection $collection, ?CollectionQuery $query): array
    {
        $pagination = $collection->pagination();

        return [
            'collection' => $collection,
            'page' => $this->page,
            'config' => $this->config,
            'pagination' => $pagination,
            'showPagination' => $pagination
                ? $pagination->total() > $pagination->limit()
                : false,
            'showIndicator' => true,
            'urlBuilder' => $this->urlBuilder,
            'hasActiveFilters' => $query
                ? $query->hasActiveFiltersOrSearch()
                : $this->detectActiveFilters(),
        ];
    }

    /**
     * Build snippet-specific data
     */
    private function buildSnippetData(
        string $key,
        array $baseData,
        Collection $collection,
        ?CollectionQuery $query
    ): array {
        switch ($key) {
            case 'items':
                return array_merge($baseData, [
                    'items' => $this->prepareItemsWithIndex($collection),
                    'isEmpty' => $query
                        ? $query->getTotalCount() === 0
                        : $collection->count() === 0,
                ]);

            default:
                return $baseData;
        }
    }

    /**
     * Prepare collection items with order indices
     */
    private function prepareItemsWithIndex(Collection $collection): array
    {
        $indexed = [];
        $index = 0;

        foreach ($collection as $item) {
            $indexed[] = (object) [
                'page' => $item,
                'orderIndex' => $index++,
            ];
        }

        return $indexed;
    }

    /**
     * Check if a feature is enabled
     */
    private function isFeatureEnabled(string $key): bool
    {
        $featureMap = [
            'search' => 'enableSearch',
            'filters' => 'enableFilters',
            'indicator' => 'enableIndicator',
            'pagination' => 'enablePagination',
        ];

        $configKey = $featureMap[$key] ?? null;

        if ($configKey === null) {
            return true; // Unknown keys are enabled by default
        }

        return $this->config[$configKey] ?? true;
    }

    /**
     * Detect if any filters are active (fallback when no query)
     */
    private function detectActiveFilters(): bool
    {
        // Check search
        $searchParam = $this->config['search']['param'] ?? 'q';
        if (get($searchParam)) {
            return true;
        }

        // Check taxonomy filters
        foreach ($this->config['taxonomies'] ?? [] as $taxonomy) {
            if (get($taxonomy['param']) ?? param($taxonomy['param'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the config
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Get the page
     */
    public function getPage(): Page
    {
        return $this->page;
    }

    /**
     * Get the URL builder
     */
    public function getUrlBuilder(): UrlBuilder
    {
        return $this->urlBuilder;
    }
}
