<?php

declare(strict_types=1);

namespace KirbyCollectionManager\Query;

use Kirby\Cms\Collection;

/**
 * Interface for collection query pipeline
 *
 * Provides a fluent interface for building collection queries
 * with search, filtering, sorting, and pagination.
 */
interface CollectionQueryInterface
{
    /**
     * Create a query from a collection
     *
     * @param Collection $collection The base collection to query
     * @return static
     */
    public static function from(Collection $collection): static;

    /**
     * Apply search to the collection
     *
     * @param string $query The search query string
     * @param array $fields Fields to search in
     * @return static
     */
    public function search(string $query, array $fields = ['title', 'text']): static;

    /**
     * Apply a filter to the collection
     *
     * @param string $field The field to filter by
     * @param mixed $value The value to filter for
     * @return static
     */
    public function filter(string $field, mixed $value): static;

    /**
     * Apply multiple filters at once
     *
     * @param array $filters Array of field => value pairs
     * @return static
     */
    public function filters(array $filters): static;

    /**
     * Sort the collection
     *
     * @param string $field The field to sort by
     * @param string $direction Sort direction ('asc' or 'desc')
     * @return static
     */
    public function sort(string $field, string $direction = 'desc'): static;

    /**
     * Paginate the collection
     *
     * @param int $limit Items per page
     * @param string $param URL parameter name for pagination
     * @return static
     */
    public function paginate(int $limit, string $param = 'p'): static;

    /**
     * Get the resulting collection
     *
     * @return Collection
     */
    public function get(): Collection;

    /**
     * Get the total count before pagination
     *
     * @return int
     */
    public function getTotalCount(): int;

    /**
     * Get debug information about the query pipeline
     *
     * @return array
     */
    public function getDebugInfo(): array;

    /**
     * Check if any search was applied
     *
     * @return bool
     */
    public function hasSearch(): bool;

    /**
     * Check if any filters were applied
     *
     * @return bool
     */
    public function hasFilters(): bool;

    /**
     * Get the current search query
     *
     * @return string|null
     */
    public function getSearchQuery(): ?string;

    /**
     * Get all applied filters
     *
     * @return array
     */
    public function getAppliedFilters(): array;
}
