<?php

declare(strict_types=1);

namespace KirbyCollectionManager\Config;

use KirbyCollectionManager\Exception\InvalidConfigurationException;

/**
 * Data Transfer Object for search configuration
 *
 * Validates and normalizes search settings with sensible defaults.
 *
 * @property-read bool $enabled Whether search is enabled
 * @property-read string $param URL parameter name for search
 * @property-read string $placeholder Placeholder text for search input
 * @property-read array $fields Fields to search in
 * @property-read int $minLength Minimum search query length
 */
final class SearchConfig
{
    private const DEFAULT_PARAM = 'q';
    private const DEFAULT_MIN_LENGTH = 2;
    private const MIN_LENGTH_FLOOR = 1;
    private const MIN_LENGTH_CEILING = 10;

    public readonly bool $enabled;
    public readonly string $param;
    public readonly string $placeholder;
    public readonly array $fields;
    public readonly int $minLength;

    /**
     * Create search config from array
     *
     * @param array $config Raw configuration array
     * @throws InvalidConfigurationException
     */
    public function __construct(array $config = [])
    {
        $this->enabled = (bool) ($config['enabled'] ?? true);
        $this->param = $this->validateParam($config['param'] ?? self::DEFAULT_PARAM);
        $this->placeholder = $this->validatePlaceholder($config['placeholder'] ?? '');
        $this->fields = $this->validateFields($config['fields'] ?? ['title', 'text']);
        $this->minLength = $this->validateMinLength($config['minLength'] ?? self::DEFAULT_MIN_LENGTH);
    }

    /**
     * Create from array (static factory)
     */
    public static function fromArray(array $config): self
    {
        return new self($config);
    }

    /**
     * Validate param name
     */
    private function validateParam(mixed $value): string
    {
        if (!is_string($value)) {
            throw InvalidConfigurationException::invalidType('search.param', $value, 'string');
        }

        $param = trim($value);

        if (empty($param)) {
            throw InvalidConfigurationException::invalidValue(
                'search.param',
                $value,
                'Parameter name cannot be empty'
            );
        }

        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $param)) {
            throw InvalidConfigurationException::invalidValue(
                'search.param',
                $value,
                'Parameter name must be a valid URL parameter'
            );
        }

        return $param;
    }

    /**
     * Validate placeholder text
     */
    private function validatePlaceholder(mixed $value): string
    {
        if (!is_string($value)) {
            throw InvalidConfigurationException::invalidType('search.placeholder', $value, 'string');
        }

        // Use translation as default if empty
        if (empty($value)) {
            return t('collection.search.placeholder', 'Search...');
        }

        return $value;
    }

    /**
     * Validate search fields
     */
    private function validateFields(mixed $value): array
    {
        if (!is_array($value)) {
            throw InvalidConfigurationException::invalidType('search.fields', $value, 'array');
        }

        if (empty($value)) {
            throw InvalidConfigurationException::invalidValue(
                'search.fields',
                $value,
                'At least one search field must be specified'
            );
        }

        foreach ($value as $field) {
            if (!is_string($field)) {
                throw InvalidConfigurationException::invalidValue(
                    'search.fields',
                    $value,
                    'All search fields must be strings'
                );
            }
        }

        return array_values($value);
    }

    /**
     * Validate minimum search length
     */
    private function validateMinLength(mixed $value): int
    {
        if (!is_int($value) && !is_numeric($value)) {
            throw InvalidConfigurationException::invalidType('search.minLength', $value, 'integer');
        }

        $minLength = (int) $value;

        if ($minLength < self::MIN_LENGTH_FLOOR || $minLength > self::MIN_LENGTH_CEILING) {
            throw InvalidConfigurationException::outOfRange(
                'search.minLength',
                $minLength,
                self::MIN_LENGTH_FLOOR,
                self::MIN_LENGTH_CEILING
            );
        }

        return $minLength;
    }

    /**
     * Check if search is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'enabled' => $this->enabled,
            'param' => $this->param,
            'placeholder' => $this->placeholder,
            'fields' => $this->fields,
            'minLength' => $this->minLength,
        ];
    }
}
