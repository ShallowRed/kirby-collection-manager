<?php

declare(strict_types=1);

namespace KirbyCollectionManager\Response;

use Kirby\Cms\Collection;

/**
 * Handles JSON AJAX responses (legacy support)
 *
 * Returns JSON data for backwards compatibility with
 * the original JavaScript implementation.
 */
final class JsonResponseHandler implements ResponseHandlerInterface
{
    /**
     * Check if this handler can handle the current request
     */
    public function canHandle(): bool
    {
        return RequestDetector::isJsonRequest();
    }

    /**
     * Get the response type identifier
     */
    public function getType(): string
    {
        return RequestDetector::TYPE_JSON;
    }

    /**
     * Handle the JSON response
     */
    public function handle(Collection $collection, array $snippets, array $config): void
    {
        header('Content-Type: application/json');

        $replacements = $this->buildReplacements($snippets, $config);

        echo json_encode([
            'success' => true,
            'snippets' => $snippets,
            'replacements' => $replacements,
            'meta' => $this->buildMeta($collection),
        ], JSON_THROW_ON_ERROR);

        exit;
    }

    /**
     * Build replacement instructions for the legacy JavaScript
     */
    private function buildReplacements(array $snippets, array $config): array
    {
        $replacements = [];
        $replacementConfig = $config['replacements'] ?? [];

        foreach ($replacementConfig as $replacement) {
            $snippetKey = $replacement['snippet'] ?? '';

            if (isset($snippets[$snippetKey])) {
                $replacements[] = array_merge($replacement, [
                    'data' => $snippets[$snippetKey],
                ]);
            }
        }

        return $replacements;
    }

    /**
     * Build metadata about the collection
     */
    private function buildMeta(Collection $collection): array
    {
        $pagination = $collection->pagination();

        return [
            'count' => $collection->count(),
            'total' => $pagination ? $pagination->total() : $collection->count(),
            'page' => $pagination ? $pagination->page() : 1,
            'pages' => $pagination ? $pagination->pages() : 1,
            'hasPrevPage' => $pagination ? $pagination->hasPrevPage() : false,
            'hasNextPage' => $pagination ? $pagination->hasNextPage() : false,
        ];
    }

    /**
     * Send an error response
     */
    public static function sendError(string $message, int $statusCode = 500): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');

        echo json_encode([
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => $statusCode,
            ],
        ], JSON_THROW_ON_ERROR);

        exit;
    }
}
