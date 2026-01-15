<?php

declare(strict_types=1);

namespace KirbyCollectionManager\Url;

use Kirby\Cms\Page;

/**
 * URL Builder for Collection Manager
 *
 * Handles URL generation with proper parameter preservation
 * for pagination, search, and filter parameters.
 */
final class UrlBuilder
{
    /**
     * The base page for URL generation
     */
    private Page $page;

    /**
     * Pagination parameter name
     */
    private string $paginationParam;

    /**
     * Search parameter name
     */
    private string $searchParam;

    /**
     * Parameters to exclude from URLs
     */
    private array $excludeParams = ['json', 'htmx'];

    /**
     * Create a new URL builder
     */
    public function __construct(
        Page $page,
        string $paginationParam = 'p',
        string $searchParam = 'q'
    ) {
        $this->page = $page;
        $this->paginationParam = $paginationParam;
        $this->searchParam = $searchParam;
    }

    /**
     * Create a URL builder instance
     */
    public static function for(
        Page $page,
        string $paginationParam = 'p',
        string $searchParam = 'q'
    ): self {
        return new self($page, $paginationParam, $searchParam);
    }

    /**
     * Build a URL with the given parameters
     *
     * Preserves current search and filter parameters while
     * applying the new parameters.
     *
     * @param array $params Parameters to set (null values remove the param)
     * @return string
     */
    public function build(array $params = []): string
    {
        $currentParams = $this->getCurrentParams();

        // Merge with new parameters (new ones override current ones)
        $allParams = array_merge($currentParams, $params);

        // Remove empty/null parameters
        $allParams = array_filter($allParams, function ($value) {
            return $value !== null && $value !== '';
        });

        $url = $this->page->url();

        if (!empty($allParams)) {
            $url .= '?' . http_build_query($allParams);
        }

        return $url;
    }

    /**
     * Build URL for a specific page number
     */
    public function forPage(?int $page): string
    {
        // Page 1 should not have pagination param
        if ($page === null || $page <= 1) {
            return $this->build([$this->paginationParam => null]);
        }

        return $this->build([$this->paginationParam => $page]);
    }

    /**
     * Build URL for search
     */
    public function forSearch(?string $query): string
    {
        return $this->build([
            $this->searchParam => $query,
            $this->paginationParam => null, // Reset pagination on search
        ]);
    }

    /**
     * Build URL to clear search
     */
    public function clearSearch(): string
    {
        return $this->build([
            $this->searchParam => null,
            $this->paginationParam => null,
        ]);
    }

    /**
     * Build URL for a filter value
     */
    public function forFilter(string $param, ?string $value): string
    {
        return $this->build([
            $param => $value,
            $this->paginationParam => null, // Reset pagination on filter change
        ]);
    }

    /**
     * Build URL to clear a specific filter
     */
    public function clearFilter(string $param): string
    {
        return $this->forFilter($param, null);
    }

    /**
     * Build URL to clear all filters
     */
    public function clearAllFilters(array $filterParams): string
    {
        $params = [$this->paginationParam => null];

        foreach ($filterParams as $param) {
            $params[$param] = null;
        }

        return $this->build($params);
    }

    /**
     * Build URL to clear everything (search, filters, pagination)
     */
    public function clearAll(array $filterParams = []): string
    {
        $params = [
            $this->searchParam => null,
            $this->paginationParam => null,
        ];

        foreach ($filterParams as $param) {
            $params[$param] = null;
        }

        return $this->build($params);
    }

    /**
     * Get current GET parameters (excluding internal ones)
     */
    public function getCurrentParams(): array
    {
        $params = [];

        foreach ($_GET as $key => $value) {
            // Skip excluded parameters
            if (in_array($key, $this->excludeParams, true)) {
                continue;
            }

            if ($value !== null && $value !== '') {
                $params[$key] = $value;
            }
        }

        return $params;
    }

    /**
     * Get current search query
     */
    public function getCurrentSearch(): ?string
    {
        $search = get($this->searchParam);
        return $search !== null && $search !== '' ? $search : null;
    }

    /**
     * Get current page number
     */
    public function getCurrentPage(): int
    {
        $page = get($this->paginationParam);
        return $page !== null ? max(1, (int) $page) : 1;
    }

    /**
     * Get current filter value
     */
    public function getCurrentFilter(string $param): ?string
    {
        $value = get($param) ?? param($param);
        return $value !== null && $value !== '' ? $value : null;
    }

    /**
     * Check if a filter is currently active
     */
    public function isFilterActive(string $param, ?string $value = null): bool
    {
        $current = $this->getCurrentFilter($param);

        if ($value === null) {
            return $current !== null;
        }

        return $current === $value;
    }

    /**
     * Get the base page
     */
    public function getPage(): Page
    {
        return $this->page;
    }

    /**
     * Get pagination parameter name
     */
    public function getPaginationParam(): string
    {
        return $this->paginationParam;
    }

    /**
     * Get search parameter name
     */
    public function getSearchParam(): string
    {
        return $this->searchParam;
    }

    /**
     * Add a parameter to the exclude list
     */
    public function exclude(string $param): self
    {
        if (!in_array($param, $this->excludeParams, true)) {
            $this->excludeParams[] = $param;
        }
        return $this;
    }
}
