<?php

declare(strict_types=1);

namespace KirbyCollectionManager\Response;

use Kirby\Cms\Collection;

/**
 * Handles htmx AJAX responses
 *
 * Returns HTML fragments that htmx will swap into the page.
 */
final class HtmxResponseHandler implements ResponseHandlerInterface
{
    /**
     * Check if this handler can handle the current request
     */
    public function canHandle(): bool
    {
        return RequestDetector::isHtmxRequest();
    }

    /**
     * Get the response type identifier
     */
    public function getType(): string
    {
        return RequestDetector::TYPE_HTMX;
    }

    /**
     * Handle the htmx response
     */
    public function handle(Collection $collection, array $snippets, array $config): void
    {
        // Set proper content type
        header('Content-Type: text/html; charset=utf-8');

        // Build HTML content from snippets
        $html = $this->buildHtml($snippets, $config);

        echo $html;
        exit;
    }

    /**
     * Build the HTML response from snippets
     */
    private function buildHtml(array $snippets, array $config): string
    {
        $html = '';
        $containers = $config['containers'] ?? [];

        // Search section
        if ($config['enableSearch'] ?? true) {
            $searchClass = $this->getContainerClass($containers['search'] ?? '.collection-search');
            $html .= '<div class="' . $searchClass . '">';
            $html .= $snippets['search'] ?? '';
            $html .= '</div>';
        }

        // Filters section
        if ($config['enableFilters'] ?? true) {
            $filtersClass = $this->getContainerClass($containers['filters'] ?? '.collection-filters');
            $html .= '<div class="' . $filtersClass . '">';
            $html .= $snippets['filters'] ?? '';
            $html .= '</div>';
        }

        // Items section
        $itemsClass = $this->getContainerClass($containers['items'] ?? '.collection-items');
        $html .= '<div class="' . $itemsClass . '" data-replacementtop="true" data-offset="100">';
        $html .= $snippets['items'] ?? '';
        $html .= '</div>';

        // Pagination and indicator wrapper
        $html .= '<div class="collection-pagination-wrapper">';
        $html .= $snippets['pagination'] ?? '';
        $html .= $snippets['indicator'] ?? '';
        $html .= '</div>';

        return $html;
    }

    /**
     * Extract class name from CSS selector
     */
    private function getContainerClass(string $selector): string
    {
        return trim($selector, '.');
    }

    /**
     * Send an error response
     */
    public static function sendError(string $message, int $statusCode = 500): void
    {
        http_response_code($statusCode);
        header('Content-Type: text/html; charset=utf-8');

        $html = '<div class="collection-error">';
        $html .= '<p class="collection-error__message">' . htmlspecialchars($message) . '</p>';
        $html .= '</div>';

        echo $html;
        exit;
    }
}
