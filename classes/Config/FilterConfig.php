<?php

declare(strict_types=1);

namespace KirbyCollectionManager\Config;

use KirbyCollectionManager\Exception\InvalidConfigurationException;

/**
 * Data Transfer Object for filter configuration
 *
 * Validates and normalizes filter/taxonomy settings with sensible defaults.
 *
 * @property-read bool $enabled Whether filters are enabled
 * @property-read array $taxonomies Taxonomy definitions
 * @property-read bool $multiSelect Whether multiple filter values can be selected
 */
final class FilterConfig
{
    public readonly bool $enabled;
    public readonly array $taxonomies;
    public readonly bool $multiSelect;

    /**
     * Create filter config from array
     *
     * @param array $config Raw configuration array
     * @throws InvalidConfigurationException
     */
    public function __construct(array $config = [])
    {
        $this->enabled = (bool) ($config['enabled'] ?? true);
        $this->taxonomies = $this->validateTaxonomies($config['taxonomies'] ?? []);
        $this->multiSelect = (bool) ($config['multiSelect'] ?? false);
    }

    /**
     * Create from array (static factory)
     */
    public static function fromArray(array $config): self
    {
        return new self($config);
    }

    /**
     * Validate taxonomies configuration
     */
    private function validateTaxonomies(mixed $value): array
    {
        if (!is_array($value)) {
            throw InvalidConfigurationException::invalidType('filter.taxonomies', $value, 'array');
        }

        $validated = [];

        foreach ($value as $key => $taxonomy) {
            $validated[$key] = $this->validateTaxonomy($key, $taxonomy);
        }

        return $validated;
    }

    /**
     * Validate a single taxonomy definition
     */
    private function validateTaxonomy(string $key, mixed $taxonomy): array
    {
        // Allow shorthand: 'category' => 'Category'
        if (is_string($taxonomy)) {
            return [
                'field' => $key,
                'label' => $taxonomy,
                'param' => $key,
                'type' => 'select',
            ];
        }

        if (!is_array($taxonomy)) {
            throw InvalidConfigurationException::invalidType(
                "filter.taxonomies.{$key}",
                $taxonomy,
                'array or string'
            );
        }

        // Validate required fields
        $field = $taxonomy['field'] ?? $key;
        if (!is_string($field)) {
            throw InvalidConfigurationException::invalidType(
                "filter.taxonomies.{$key}.field",
                $field,
                'string'
            );
        }

        $label = $taxonomy['label'] ?? ucfirst($key);
        if (!is_string($label)) {
            throw InvalidConfigurationException::invalidType(
                "filter.taxonomies.{$key}.label",
                $label,
                'string'
            );
        }

        $param = $taxonomy['param'] ?? $key;
        if (!is_string($param)) {
            throw InvalidConfigurationException::invalidType(
                "filter.taxonomies.{$key}.param",
                $param,
                'string'
            );
        }

        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $param)) {
            throw InvalidConfigurationException::invalidValue(
                "filter.taxonomies.{$key}.param",
                $param,
                'Parameter name must be a valid URL parameter'
            );
        }

        $type = $taxonomy['type'] ?? 'select';
        $validTypes = ['select', 'checkbox', 'tags'];
        if (!in_array($type, $validTypes, true)) {
            throw InvalidConfigurationException::invalidValue(
                "filter.taxonomies.{$key}.type",
                $type,
                'Type must be one of: ' . implode(', ', $validTypes)
            );
        }

        return [
            'field' => $field,
            'label' => $label,
            'param' => $param,
            'type' => $type,
            'options' => $taxonomy['options'] ?? null,
        ];
    }

    /**
     * Check if filters are enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled && !empty($this->taxonomies);
    }

    /**
     * Get taxonomy names
     */
    public function getTaxonomyNames(): array
    {
        return array_keys($this->taxonomies);
    }

    /**
     * Get a specific taxonomy config
     */
    public function getTaxonomy(string $name): ?array
    {
        return $this->taxonomies[$name] ?? null;
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'enabled' => $this->enabled,
            'taxonomies' => $this->taxonomies,
            'multiSelect' => $this->multiSelect,
        ];
    }
}
