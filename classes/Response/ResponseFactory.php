<?php

declare(strict_types=1);

namespace KirbyCollectionManager\Response;

use Kirby\Cms\Collection;

/**
 * Factory for creating response handlers
 *
 * Determines the appropriate response handler based on
 * the current request type.
 */
final class ResponseFactory
{
    /**
     * Available handlers
     *
     * @var ResponseHandlerInterface[]
     */
    private static array $handlers = [];

    /**
     * Get the appropriate handler for the current request
     *
     * @return ResponseHandlerInterface|null
     */
    public static function getHandler(): ?ResponseHandlerInterface
    {
        $handlers = self::getHandlers();

        foreach ($handlers as $handler) {
            if ($handler->canHandle()) {
                return $handler;
            }
        }

        return null;
    }

    /**
     * Check if the current request needs AJAX handling
     */
    public static function isAjaxRequest(): bool
    {
        return self::getHandler() !== null;
    }

    /**
     * Handle the AJAX response if applicable
     *
     * @return bool True if handled, false if not an AJAX request
     */
    public static function handleIfAjax(
        Collection $collection,
        array $snippets,
        array $config
    ): bool {
        $handler = self::getHandler();

        if ($handler === null) {
            return false;
        }

        $handler->handle($collection, $snippets, $config);
        return true; // Won't reach here due to exit, but for clarity
    }

    /**
     * Get the current request type
     */
    public static function getRequestType(): string
    {
        return RequestDetector::detect();
    }

    /**
     * Get all registered handlers
     *
     * @return ResponseHandlerInterface[]
     */
    private static function getHandlers(): array
    {
        if (empty(self::$handlers)) {
            self::$handlers = [
                new HtmxResponseHandler(),
                new JsonResponseHandler(),
            ];
        }

        return self::$handlers;
    }

    /**
     * Register a custom handler
     *
     * Custom handlers are checked before built-in ones.
     */
    public static function registerHandler(ResponseHandlerInterface $handler): void
    {
        array_unshift(self::$handlers, $handler);
    }

    /**
     * Clear all handlers (mainly for testing)
     */
    public static function clearHandlers(): void
    {
        self::$handlers = [];
    }

    /**
     * Send an error response appropriate for the current request type
     */
    public static function sendError(string $message, int $statusCode = 500): void
    {
        $type = RequestDetector::detect();

        switch ($type) {
            case RequestDetector::TYPE_HTMX:
                HtmxResponseHandler::sendError($message, $statusCode);
                break;
            case RequestDetector::TYPE_JSON:
                JsonResponseHandler::sendError($message, $statusCode);
                break;
            default:
                // For standard requests, let Kirby handle it
                throw new \RuntimeException($message, $statusCode);
        }
    }
}
