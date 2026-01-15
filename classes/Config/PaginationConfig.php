<?php

declare(strict_types=1);

namespace KirbyCollectionManager\Config;

use KirbyCollectionManager\Exception\InvalidConfigurationException;

/**
 * Data Transfer Object for pagination configuration
 *
 * Validates and normalizes pagination settings with sensible defaults.
 *
 * @property-read int $limit Number of items per page
 * @property-read string $param URL parameter name for pagination
 * @property-read int $range Number of page numbers to display
 * @property-read bool $showInfo Whether to show page indicator
 * @property-read array $cssClasses CSS classes for pagination elements
 */
final class PaginationConfig
{
    private const DEFAULT_LIMIT = 10;
    private const DEFAULT_PARAM = 'p';
    private const DEFAULT_RANGE = 5;
    private const MIN_LIMIT = 1;
    private const MAX_LIMIT = 100;
    private const MIN_RANGE = 1;
    private const MAX_RANGE = 20;

    public readonly int $limit;
    public readonly string $param;
    public readonly int $range;
    public readonly bool $showInfo;
    public readonly array $cssClasses;

    /**
     * Create pagination config from array
     *
     * @param array $config Raw configuration array
     * @throws InvalidConfigurationException
     */
    public function __construct(array $config = [])
    {
        $this->limit = $this->validateLimit($config['limit'] ?? self::DEFAULT_LIMIT);
        $this->param = $this->validateParam($config['param'] ?? self::DEFAULT_PARAM);
        $this->range = $this->validateRange($config['range'] ?? self::DEFAULT_RANGE);
        $this->showInfo = (bool) ($config['showInfo'] ?? true);
        $this->cssClasses = $this->validateCssClasses($config['cssClasses'] ?? []);
    }

    /**
     * Create from array (static factory)
     */
    public static function fromArray(array $config): self
    {
        return new self($config);
    }

    /**
     * Validate limit value
     */
    private function validateLimit(mixed $value): int
    {
        if (!is_int($value) && !is_numeric($value)) {
            throw InvalidConfigurationException::invalidType('pagination.limit', $value, 'integer');
        }

        $limit = (int) $value;

        if ($limit < self::MIN_LIMIT || $limit > self::MAX_LIMIT) {
            throw InvalidConfigurationException::outOfRange(
                'pagination.limit',
                $limit,
                self::MIN_LIMIT,
                self::MAX_LIMIT
            );
        }

        return $limit;
    }

    /**
     * Validate param name
     */
    private function validateParam(mixed $value): string
    {
        if (!is_string($value)) {
            throw InvalidConfigurationException::invalidType('pagination.param', $value, 'string');
        }

        $param = trim($value);

        if (empty($param)) {
            throw InvalidConfigurationException::invalidValue(
                'pagination.param',
                $value,
                'Parameter name cannot be empty'
            );
        }

        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $param)) {
            throw InvalidConfigurationException::invalidValue(
                'pagination.param',
                $value,
                'Parameter name must be a valid URL parameter (alphanumeric, starting with letter or underscore)'
            );
        }

        return $param;
    }

    /**
     * Validate range value
     */
    private function validateRange(mixed $value): int
    {
        if (!is_int($value) && !is_numeric($value)) {
            throw InvalidConfigurationException::invalidType('pagination.range', $value, 'integer');
        }

        $range = (int) $value;

        if ($range < self::MIN_RANGE || $range > self::MAX_RANGE) {
            throw InvalidConfigurationException::outOfRange(
                'pagination.range',
                $range,
                self::MIN_RANGE,
                self::MAX_RANGE
            );
        }

        return $range;
    }

    /**
     * Validate CSS classes
     */
    private function validateCssClasses(mixed $value): array
    {
        if (!is_array($value)) {
            throw InvalidConfigurationException::invalidType('pagination.cssClasses', $value, 'array');
        }

        $defaults = [
            'nav' => 'collection-pagination',
            'item' => 'collection-pagination__item',
            'icon' => 'collection-pagination__icon',
        ];

        return array_merge($defaults, $value);
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'limit' => $this->limit,
            'param' => $this->param,
            'range' => $this->range,
            'showInfo' => $this->showInfo,
            'cssClasses' => $this->cssClasses,
        ];
    }
}
