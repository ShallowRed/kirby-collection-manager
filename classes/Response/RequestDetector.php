<?php

declare(strict_types=1);

namespace KirbyCollectionManager\Response;

/**
 * Detects the type of request being made
 *
 * Determines whether the request is a standard page load,
 * an htmx request, or a legacy JSON AJAX request.
 */
final class RequestDetector
{
    /**
     * Request type constants
     */
    public const TYPE_STANDARD = 'standard';
    public const TYPE_HTMX = 'htmx';
    public const TYPE_JSON = 'json';

    /**
     * Detect the request type
     *
     * @return string One of the TYPE_* constants
     */
    public static function detect(): string
    {
        if (self::isHtmxRequest()) {
            return self::TYPE_HTMX;
        }

        if (self::isJsonRequest()) {
            return self::TYPE_JSON;
        }

        return self::TYPE_STANDARD;
    }

    /**
     * Check if this is an htmx request
     */
    public static function isHtmxRequest(): bool
    {
        // Check for htmx query parameter
        if (get('htmx')) {
            return true;
        }

        // Check for HX-Request header
        if (isset($_SERVER['HTTP_HX_REQUEST'])) {
            return true;
        }

        return false;
    }

    /**
     * Check if this is a JSON request
     */
    public static function isJsonRequest(): bool
    {
        // Must have json parameter
        if (!get('json')) {
            return false;
        }

        // Must be XHR or accept JSON
        $isXhr = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
        $acceptsJson = strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;

        return $isXhr || $acceptsJson;
    }

    /**
     * Check if this is an AJAX request of any type
     */
    public static function isAjaxRequest(): bool
    {
        return self::detect() !== self::TYPE_STANDARD;
    }

    /**
     * Get htmx-specific request info
     */
    public static function getHtmxInfo(): array
    {
        return [
            'request' => $_SERVER['HTTP_HX_REQUEST'] ?? null,
            'trigger' => $_SERVER['HTTP_HX_TRIGGER'] ?? null,
            'triggerName' => $_SERVER['HTTP_HX_TRIGGER_NAME'] ?? null,
            'target' => $_SERVER['HTTP_HX_TARGET'] ?? null,
            'currentUrl' => $_SERVER['HTTP_HX_CURRENT_URL'] ?? null,
            'prompt' => $_SERVER['HTTP_HX_PROMPT'] ?? null,
            'boosted' => isset($_SERVER['HTTP_HX_BOOSTED']),
            'historyRestore' => isset($_SERVER['HTTP_HX_HISTORY_RESTORE_REQUEST']),
        ];
    }
}
