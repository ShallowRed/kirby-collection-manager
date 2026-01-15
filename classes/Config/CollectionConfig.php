<?php

declare(strict_types=1);

namespace KirbyCollectionManager\Config;

use KirbyCollectionManager\Exception\InvalidConfigurationException;
use Closure;

/**
 * Main configuration container for Collection Manager
 *
 * Aggregates all sub-configurations (pagination, search, filters)
 * and provides validation for the complete configuration.
 *
 * @property-read PaginationConfig $pagination
 * @property-read SearchConfig $search
 * @property-read FilterConfig $filter
 * @property-read Closure|null $collection
 * @property-read bool $enableJs
 * @property-read array $snippets
 * @property-read string|null $sortBy
 * @property-read string $sortDirection
 */
final class CollectionConfig
{
    public readonly PaginationConfig $pagination;
    public readonly SearchConfig $search;
    public readonly FilterConfig $filter;
    public readonly ?Closure $collection;
    public readonly bool $enableJs;
    public readonly array $snippets;
    public readonly ?string $sortBy;
    public readonly string $sortDirection;

    /**
     * Create collection config from array
     *
     * @param array $config Raw configuration array
     * @throws InvalidConfigurationException
     */
    public function __construct(array $config = [])
    {
        $this->pagination = new PaginationConfig($config['pagination'] ?? []);
        $this->search = new SearchConfig($config['search'] ?? []);
        $this->filter = new FilterConfig($config['filter'] ?? $config['filters'] ?? []);
        $this->collection = $this->validateCollection($config['collection'] ?? null);
        $this->enableJs = (bool) ($config['enableJs'] ?? true);
        $this->snippets = $this->validateSnippets($config['snippets'] ?? []);
        $this->sortBy = $this->validateSortBy($config['sortBy'] ?? null);
        $this->sortDirection = $this->validateSortDirection($config['sortDirection'] ?? 'desc');
    }

    /**
     * Create from array (static factory)
     */
    public static function fromArray(array $config): self
    {
        return new self($config);
    }

    /**
     * Validate collection closure
     */
    private function validateCollection(mixed $value): ?Closure
    {
        if ($value === null) {
            return null;
        }

        if (!($value instanceof Closure) && !is_callable($value)) {
            throw InvalidConfigurationException::invalidCallback('collection');
        }

        if (is_callable($value) && !($value instanceof Closure)) {
            return Closure::fromCallable($value);
        }

        return $value;
    }

    /**
     * Validate snippets configuration
     */
    private function validateSnippets(mixed $value): array
    {
        if (!is_array($value)) {
            throw InvalidConfigurationException::invalidType('snippets', $value, 'array');
        }

        $defaults = [
            'item' => 'collection-item',
            'items' => 'collection-items',
            'pagination' => 'collection-pagination',
            'search' => 'collection-search',
            'filters' => 'collection-filters',
        ];

        return array_merge($defaults, $value);
    }

    /**
     * Validate sortBy field
     */
    private function validateSortBy(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            throw InvalidConfigurationException::invalidType('sortBy', $value, 'string');
        }

        return $value;
    }

    /**
     * Validate sort direction
     */
    private function validateSortDirection(mixed $value): string
    {
        if (!is_string($value)) {
            throw InvalidConfigurationException::invalidType('sortDirection', $value, 'string');
        }

        $value = strtolower($value);
        $valid = ['asc', 'desc'];

        if (!in_array($value, $valid, true)) {
            throw InvalidConfigurationException::invalidValue(
                'sortDirection',
                $value,
                'Must be "asc" or "desc"'
            );
        }

        return $value;
    }

    /**
     * Check if JavaScript/htmx is enabled
     */
    public function isJsEnabled(): bool
    {
        return $this->enableJs;
    }

    /**
     * Get snippet name for a component
     */
    public function getSnippet(string $component): string
    {
        return $this->snippets[$component] ?? "collection-{$component}";
    }

    /**
     * Convert to array (for backwards compatibility)
     */
    public function toArray(): array
    {
        return [
            'pagination' => $this->pagination->toArray(),
            'search' => $this->search->toArray(),
            'filter' => $this->filter->toArray(),
            'enableJs' => $this->enableJs,
            'snippets' => $this->snippets,
            'sortBy' => $this->sortBy,
            'sortDirection' => $this->sortDirection,
        ];
    }

    /**
     * Merge with additional configuration
     *
     * Creates a new instance with merged values
     */
    public function merge(array $overrides): self
    {
        return new self(array_replace_recursive($this->toArray(), $overrides));
    }
}
