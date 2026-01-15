<?php

declare(strict_types=1);

namespace KirbyCollectionManager\Query;

use Kirby\Cms\Collection;
use Kirby\Cms\Pages;

/**
 * Collection Query Pipeline
 *
 * Provides a fluent interface for building collection queries
 * with search, filtering, sorting, and pagination.
 *
 * @example
 * $result = CollectionQuery::from($page->children()->listed())
 *     ->search('hello', ['title', 'text'])
 *     ->filter('category', 'news')
 *     ->sort('date', 'desc')
 *     ->paginate(10, 'p')
 *     ->get();
 */
final class CollectionQuery implements CollectionQueryInterface
{
    /**
     * The collection being queried
     */
    private Collection $collection;

    /**
     * Total count before pagination
     */
    private int $totalCount = 0;

    /**
     * Debug info for each stage
     */
    private array $debugInfo = [];

    /**
     * Search query if applied
     */
    private ?string $searchQuery = null;

    /**
     * Search fields
     */
    private array $searchFields = [];

    /**
     * Applied filters
     */
    private array $appliedFilters = [];

    /**
     * Sort field
     */
    private ?string $sortField = null;

    /**
     * Sort direction
     */
    private string $sortDirection = 'desc';

    /**
     * Whether pagination was applied
     */
    private bool $isPaginated = false;

    /**
     * Create a new query instance
     */
    private function __construct(Collection $collection)
    {
        $this->collection = $collection;
        $this->debugInfo['initial'] = [
            'count' => $collection->count(),
            'type' => get_class($collection),
        ];
    }

    /**
     * Create a query from a collection
     */
    public static function from(Collection $collection): static
    {
        return new static($collection);
    }

    /**
     * Apply search to the collection
     */
    public function search(string $query, array $fields = ['title', 'text']): static
    {
        $query = trim($query);

        if (empty($query)) {
            return $this;
        }

        $this->searchQuery = $query;
        $this->searchFields = $fields;

        $searchString = implode('|', $fields);
        $beforeCount = $this->collection->count();

        $this->collection = $this->collection->search($query, $searchString);

        $this->debugInfo['search'] = [
            'query' => $query,
            'fields' => $fields,
            'beforeCount' => $beforeCount,
            'afterCount' => $this->collection->count(),
            'removed' => $beforeCount - $this->collection->count(),
        ];

        return $this;
    }

    /**
     * Apply a filter to the collection
     */
    public function filter(string $field, mixed $value): static
    {
        if ($value === null || $value === '') {
            return $this;
        }

        $beforeCount = $this->collection->count();

        $this->collection = $this->collection->filterBy($field, $value);
        $this->appliedFilters[$field] = $value;

        $this->debugInfo['filters'][] = [
            'field' => $field,
            'value' => $value,
            'beforeCount' => $beforeCount,
            'afterCount' => $this->collection->count(),
            'removed' => $beforeCount - $this->collection->count(),
        ];

        return $this;
    }

    /**
     * Apply multiple filters at once
     */
    public function filters(array $filters): static
    {
        foreach ($filters as $field => $value) {
            $this->filter($field, $value);
        }

        return $this;
    }

    /**
     * Sort the collection
     */
    public function sort(string $field, string $direction = 'desc'): static
    {
        $direction = strtolower($direction);

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $this->sortField = $field;
        $this->sortDirection = $direction;

        $this->collection = $this->collection->sortBy($field, $direction);

        $this->debugInfo['sort'] = [
            'field' => $field,
            'direction' => $direction,
        ];

        return $this;
    }

    /**
     * Paginate the collection
     */
    public function paginate(int $limit, string $param = 'p'): static
    {
        // Store total count before pagination
        $this->totalCount = $this->collection->count();

        $this->collection = $this->collection->paginate([
            'limit' => max(1, $limit),
            'method' => 'query',
            'variable' => $param,
        ]);

        $this->isPaginated = true;

        $pagination = $this->collection->pagination();

        $this->debugInfo['pagination'] = [
            'limit' => $limit,
            'param' => $param,
            'totalCount' => $this->totalCount,
            'currentPage' => $pagination ? $pagination->page() : 1,
            'totalPages' => $pagination ? $pagination->pages() : 1,
            'itemsOnPage' => $this->collection->count(),
        ];

        return $this;
    }

    /**
     * Get the resulting collection
     */
    public function get(): Collection
    {
        // Store total if not paginated
        if (!$this->isPaginated) {
            $this->totalCount = $this->collection->count();
        }

        return $this->collection;
    }

    /**
     * Get the total count before pagination
     */
    public function getTotalCount(): int
    {
        if (!$this->isPaginated) {
            return $this->collection->count();
        }

        return $this->totalCount;
    }

    /**
     * Get debug information about the query pipeline
     */
    public function getDebugInfo(): array
    {
        return [
            'stages' => $this->debugInfo,
            'summary' => [
                'hasSearch' => $this->hasSearch(),
                'hasFilters' => $this->hasFilters(),
                'searchQuery' => $this->searchQuery,
                'filterCount' => count($this->appliedFilters),
                'sortField' => $this->sortField,
                'sortDirection' => $this->sortDirection,
                'isPaginated' => $this->isPaginated,
                'totalCount' => $this->getTotalCount(),
                'resultCount' => $this->collection->count(),
            ],
        ];
    }

    /**
     * Check if any search was applied
     */
    public function hasSearch(): bool
    {
        return $this->searchQuery !== null;
    }

    /**
     * Check if any filters were applied
     */
    public function hasFilters(): bool
    {
        return !empty($this->appliedFilters);
    }

    /**
     * Get the current search query
     */
    public function getSearchQuery(): ?string
    {
        return $this->searchQuery;
    }

    /**
     * Get all applied filters
     */
    public function getAppliedFilters(): array
    {
        return $this->appliedFilters;
    }

    /**
     * Check if collection is empty
     */
    public function isEmpty(): bool
    {
        return $this->collection->count() === 0;
    }

    /**
     * Get pagination object if available
     */
    public function getPagination()
    {
        return $this->collection->pagination();
    }

    /**
     * Check if any filters or search are active
     */
    public function hasActiveFiltersOrSearch(): bool
    {
        return $this->hasSearch() || $this->hasFilters();
    }
}
