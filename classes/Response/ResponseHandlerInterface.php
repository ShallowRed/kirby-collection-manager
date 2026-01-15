<?php

declare(strict_types=1);

namespace KirbyCollectionManager\Response;

use Kirby\Cms\Collection;

/**
 * Interface for response handlers
 *
 * Defines the contract for handling different types of responses
 * (HTML, htmx, JSON, etc.)
 */
interface ResponseHandlerInterface
{
    /**
     * Check if this handler can handle the current request
     */
    public function canHandle(): bool;

    /**
     * Handle the response
     *
     * @param Collection $collection The processed collection
     * @param array $snippets Generated HTML snippets
     * @param array $config Plugin configuration
     * @return void
     */
    public function handle(Collection $collection, array $snippets, array $config): void;

    /**
     * Get the response type identifier
     */
    public function getType(): string;
}
