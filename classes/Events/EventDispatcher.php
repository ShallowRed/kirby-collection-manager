<?php

declare(strict_types=1);

namespace KirbyCollectionManager\Events;

use Kirby\Cms\Collection;

/**
 * Event dispatcher for Collection Manager hooks.
 *
 * Integrates with Kirby's hook system to allow plugins and site code
 * to hook into the collection processing lifecycle.
 *
 * Available hooks:
 * - collection-manager.config.resolved - After config is parsed
 * - collection-manager.query.before - Before collection query
 * - collection-manager.query.after - After collection query
 * - collection-manager.snippets.before - Before snippet rendering
 * - collection-manager.snippets.after - After snippet rendering
 * - collection-manager.response.before - Before AJAX response
 *
 * @example
 * ```php
 * // In site/config/config.php
 * return [
 *     'hooks' => [
 *         'collection-manager.query.before' => function ($collection, $config) {
 *             // Filter out unpublished items
 *             return $collection->filter(fn($item) => $item->isListed());
 *         },
 *         'collection-manager.query.after' => function ($collection, $debug) {
 *             // Log for analytics
 *             Analytics::log('collection_query', $debug);
 *         }
 *     ]
 * ];
 * ```
 */
class EventDispatcher
{
    /** @var array<string, array<callable>> Local event listeners */
    private array $listeners = [];

    /** @var bool Whether events are enabled */
    private bool $enabled;

    /** @var array<string> List of valid hook names */
    private const VALID_HOOKS = [
        'collection-manager.config.resolved',
        'collection-manager.query.before',
        'collection-manager.query.after',
        'collection-manager.snippets.before',
        'collection-manager.snippets.after',
        'collection-manager.response.before',
    ];

    public function __construct(?bool $enabled = null)
    {
        $this->enabled = $enabled ?? option('kirby-collection-manager.events', true);
    }

    /**
     * Check if events are enabled.
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Dispatch an event through Kirby's hook system.
     *
     * @param string $name The event name (without 'collection-manager.' prefix)
     * @param mixed ...$args Arguments to pass to the hook
     * @return mixed The return value from the hook chain (if applicable)
     */
    public function dispatch(string $name, mixed ...$args): mixed
    {
        if (!$this->enabled) {
            return $args[0] ?? null;
        }

        $hookName = $this->normalizeHookName($name);

        // First dispatch to local listeners
        $result = $this->dispatchLocal($hookName, ...$args);
        if ($result !== null) {
            $args[0] = $result;
        }

        // Then dispatch to Kirby's hook system
        if (function_exists('kirby')) {
            $kirby = kirby();
            if ($kirby && method_exists($kirby, 'trigger')) {
                $hookResult = $kirby->trigger($hookName, $args);
                if ($hookResult !== null) {
                    return $hookResult;
                }
            }
        }

        return $args[0] ?? null;
    }

    /**
     * Add a local event listener.
     *
     * @param string $name The event name
     * @param callable $callback The callback to execute
     */
    public function on(string $name, callable $callback): self
    {
        $hookName = $this->normalizeHookName($name);

        if (!isset($this->listeners[$hookName])) {
            $this->listeners[$hookName] = [];
        }

        $this->listeners[$hookName][] = $callback;
        return $this;
    }

    /**
     * Remove all local listeners for an event.
     */
    public function off(string $name): self
    {
        $hookName = $this->normalizeHookName($name);
        unset($this->listeners[$hookName]);
        return $this;
    }

    /**
     * Dispatch config resolved event.
     *
     * @param array $config The resolved configuration
     * @return array The potentially modified configuration
     */
    public function dispatchConfigResolved(array $config): array
    {
        $result = $this->dispatch('config.resolved', $config);
        return is_array($result) ? $result : $config;
    }

    /**
     * Dispatch query before event.
     *
     * @param Collection $collection The collection before querying
     * @param array $config The query configuration
     * @return Collection The potentially modified collection
     */
    public function dispatchQueryBefore(Collection $collection, array $config): Collection
    {
        $result = $this->dispatch('query.before', $collection, $config);
        return $result instanceof Collection ? $result : $collection;
    }

    /**
     * Dispatch query after event.
     *
     * @param Collection $collection The collection after querying
     * @param array $debug Debug information
     * @return Collection The potentially modified collection
     */
    public function dispatchQueryAfter(Collection $collection, array $debug = []): Collection
    {
        $result = $this->dispatch('query.after', $collection, $debug);
        return $result instanceof Collection ? $result : $collection;
    }

    /**
     * Dispatch snippets before event.
     *
     * @param Collection $collection The collection to render
     * @param array $config The render configuration
     * @return array{Collection, array} The potentially modified collection and config
     */
    public function dispatchSnippetsBefore(Collection $collection, array $config): array
    {
        $result = $this->dispatch('snippets.before', $collection, $config);
        if (is_array($result) && count($result) === 2) {
            return $result;
        }
        return [$collection, $config];
    }

    /**
     * Dispatch snippets after event.
     *
     * @param array $snippets The generated snippets
     * @param Collection $collection The source collection
     * @return array The potentially modified snippets
     */
    public function dispatchSnippetsAfter(array $snippets, Collection $collection): array
    {
        $result = $this->dispatch('snippets.after', $snippets, $collection);
        return is_array($result) ? $result : $snippets;
    }

    /**
     * Dispatch response before event.
     *
     * @param array $response The response data
     * @param string $type The response type (htmx, json, html)
     * @return array The potentially modified response
     */
    public function dispatchResponseBefore(array $response, string $type): array
    {
        $result = $this->dispatch('response.before', $response, $type);
        return is_array($result) ? $result : $response;
    }

    /**
     * Get all registered local listeners.
     */
    public function getListeners(): array
    {
        return $this->listeners;
    }

    /**
     * Get valid hook names.
     */
    public static function getValidHooks(): array
    {
        return self::VALID_HOOKS;
    }

    /**
     * Dispatch to local listeners.
     */
    private function dispatchLocal(string $hookName, mixed ...$args): mixed
    {
        if (!isset($this->listeners[$hookName])) {
            return null;
        }

        $result = null;
        foreach ($this->listeners[$hookName] as $callback) {
            $callbackResult = $callback(...$args);
            if ($callbackResult !== null) {
                $result = $callbackResult;
                // Update first argument for next callback in chain
                $args[0] = $result;
            }
        }

        return $result;
    }

    /**
     * Normalize hook name to full format.
     */
    private function normalizeHookName(string $name): string
    {
        if (str_starts_with($name, 'collection-manager.')) {
            return $name;
        }
        return 'collection-manager.' . $name;
    }

    /**
     * Create a disabled dispatcher.
     */
    public static function disabled(): self
    {
        return new self(false);
    }
}
