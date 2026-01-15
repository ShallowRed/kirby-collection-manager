<?php

declare(strict_types=1);

namespace KirbyCollectionManager\Debug;

/**
 * Collects debug information during collection processing.
 * 
 * This class provides timing, logging, and metrics collection
 * for debugging collection queries. Only active when debug mode is enabled.
 * 
 * @example
 * ```php
 * $debug = new DebugCollector();
 * $debug->startTimer('search');
 * // ... perform search
 * $debug->endTimer('search');
 * $debug->log('searchQuery', 'hello world');
 * $info = $debug->toArray();
 * ```
 */
class DebugCollector
{
    /** @var array<string, float> Timer start times */
    private array $timers = [];

    /** @var array<string, float> Completed timer durations in milliseconds */
    private array $durations = [];

    /** @var array<string, mixed> Logged debug data */
    private array $logs = [];

    /** @var float Overall start time */
    private float $startTime;

    /** @var bool Whether debug mode is enabled */
    private bool $enabled;

    /**
     * Create a new debug collector.
     *
     * @param bool|null $enabled Whether debug is enabled. If null, uses plugin option.
     */
    public function __construct(?bool $enabled = null)
    {
        $this->enabled = $enabled ?? option('kirby-collection-manager.debug', false);
        $this->startTime = microtime(true);
    }

    /**
     * Check if debug mode is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Start a named timer.
     *
     * @param string $label Unique identifier for the timer
     */
    public function startTimer(string $label): self
    {
        if (!$this->enabled) {
            return $this;
        }

        $this->timers[$label] = microtime(true);
        return $this;
    }

    /**
     * End a named timer and record duration.
     *
     * @param string $label The timer label to end
     */
    public function endTimer(string $label): self
    {
        if (!$this->enabled || !isset($this->timers[$label])) {
            return $this;
        }

        $this->durations[$label] = (microtime(true) - $this->timers[$label]) * 1000;
        unset($this->timers[$label]);
        return $this;
    }

    /**
     * Log a key-value pair for debugging.
     *
     * @param string $key The log key
     * @param mixed $value The value to log
     */
    public function log(string $key, mixed $value): self
    {
        if (!$this->enabled) {
            return $this;
        }

        $this->logs[$key] = $value;
        return $this;
    }

    /**
     * Log search information.
     */
    public function logSearch(?string $query, array $fields): self
    {
        if (!$this->enabled) {
            return $this;
        }

        $this->logs['search'] = [
            'query' => $query,
            'fields' => $fields,
            'hasQuery' => !empty($query),
        ];
        return $this;
    }

    /**
     * Log filter information.
     */
    public function logFilters(array $appliedFilters, array $availableFilters): self
    {
        if (!$this->enabled) {
            return $this;
        }

        $this->logs['filters'] = [
            'applied' => $appliedFilters,
            'available' => array_keys($availableFilters),
            'activeCount' => count($appliedFilters),
        ];
        return $this;
    }

    /**
     * Log sorting information.
     */
    public function logSort(string $field, string $direction): self
    {
        if (!$this->enabled) {
            return $this;
        }

        $this->logs['sort'] = [
            'field' => $field,
            'direction' => $direction,
        ];
        return $this;
    }

    /**
     * Log pagination information.
     */
    public function logPagination(int $totalItems, int $itemsPerPage, int $currentPage, int $totalPages): self
    {
        if (!$this->enabled) {
            return $this;
        }

        $this->logs['pagination'] = [
            'totalItems' => $totalItems,
            'itemsPerPage' => $itemsPerPage,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
        ];
        return $this;
    }

    /**
     * Log collection counts at various stages.
     */
    public function logCounts(int $initial, int $afterSearch, int $afterFilters, int $afterPagination): self
    {
        if (!$this->enabled) {
            return $this;
        }

        $this->logs['counts'] = [
            'initial' => $initial,
            'afterSearch' => $afterSearch,
            'afterFilters' => $afterFilters,
            'afterPagination' => $afterPagination,
        ];
        return $this;
    }

    /**
     * Get all debug information as an array.
     *
     * @return array{
     *   enabled: bool,
     *   executionTime: array<string, string>,
     *   logs: array<string, mixed>,
     *   timestamp: string
     * }
     */
    public function toArray(): array
    {
        if (!$this->enabled) {
            return ['enabled' => false];
        }

        $totalTime = (microtime(true) - $this->startTime) * 1000;

        // Format durations
        $executionTime = ['total' => $this->formatTime($totalTime)];
        foreach ($this->durations as $label => $duration) {
            $executionTime[$label] = $this->formatTime($duration);
        }

        return [
            'enabled' => true,
            'executionTime' => $executionTime,
            'logs' => $this->logs,
            'timestamp' => date('Y-m-d H:i:s.u'),
            'memory' => $this->formatMemory(memory_get_peak_usage(true)),
        ];
    }

    /**
     * Get debug info formatted for console output (JavaScript).
     *
     * @return string JavaScript code to log debug info
     */
    public function toConsoleScript(): string
    {
        if (!$this->enabled) {
            return '';
        }

        $data = $this->toArray();
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return <<<JS
<script>
(function() {
    const debug = $json;
    console.group('%c📊 Collection Manager Debug', 'font-weight: bold; color: #4CAF50;');
    console.log('%cExecution Time:', 'font-weight: bold;', debug.executionTime);
    if (debug.logs.search) console.log('%cSearch:', 'font-weight: bold;', debug.logs.search);
    if (debug.logs.filters) console.log('%cFilters:', 'font-weight: bold;', debug.logs.filters);
    if (debug.logs.sort) console.log('%cSort:', 'font-weight: bold;', debug.logs.sort);
    if (debug.logs.pagination) console.log('%cPagination:', 'font-weight: bold;', debug.logs.pagination);
    if (debug.logs.counts) console.log('%cCounts:', 'font-weight: bold;', debug.logs.counts);
    console.log('%cMemory:', 'font-weight: bold;', debug.memory);
    console.log('%cFull Debug Data:', 'color: #888;', debug);
    console.groupEnd();
})();
</script>
JS;
    }

    /**
     * Get debug info as HTML comment (for non-JS debugging).
     */
    public function toHtmlComment(): string
    {
        if (!$this->enabled) {
            return '';
        }

        $data = $this->toArray();
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return "<!-- Collection Manager Debug\n{$json}\n-->";
    }

    /**
     * Format time in milliseconds with appropriate unit.
     */
    private function formatTime(float $ms): string
    {
        if ($ms < 1) {
            return round($ms * 1000, 2) . 'µs';
        }
        if ($ms < 1000) {
            return round($ms, 2) . 'ms';
        }
        return round($ms / 1000, 2) . 's';
    }

    /**
     * Format memory usage.
     */
    private function formatMemory(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Create a disabled collector (for production).
     */
    public static function disabled(): self
    {
        return new self(false);
    }

    /**
     * Create an enabled collector (for testing/development).
     */
    public static function enabled(): self
    {
        return new self(true);
    }
}
