<?php

declare(strict_types=1);

namespace KirbyCollectionManager\Exception;

/**
 * Exception thrown when configuration is invalid
 *
 * This exception is thrown when:
 * - Required configuration options are missing
 * - Configuration values are of incorrect type
 * - Configuration values are out of valid range
 * - Invalid combinations of options are provided
 */
class InvalidConfigurationException extends CollectionException
{
    /**
     * The configuration key that is invalid
     */
    protected string $configKey = '';

    /**
     * The invalid value that was provided
     */
    protected mixed $invalidValue = null;

    /**
     * Expected type or format
     */
    protected string $expectedType = '';

    /**
     * Create a new invalid configuration exception
     */
    public function __construct(
        string $message = '',
        string $configKey = '',
        mixed $invalidValue = null,
        string $expectedType = '',
        int $code = 0
    ) {
        $context = [];

        if ($configKey !== '') {
            $context['configKey'] = $configKey;
            $this->configKey = $configKey;
        }

        if ($invalidValue !== null) {
            $context['invalidValue'] = $invalidValue;
            $this->invalidValue = $invalidValue;
        }

        if ($expectedType !== '') {
            $context['expectedType'] = $expectedType;
            $this->expectedType = $expectedType;
        }

        parent::__construct($message, $code, null, $context);
    }

    /**
     * Get the invalid configuration key
     */
    public function getConfigKey(): string
    {
        return $this->configKey;
    }

    /**
     * Get the invalid value
     */
    public function getInvalidValue(): mixed
    {
        return $this->invalidValue;
    }

    /**
     * Get the expected type
     */
    public function getExpectedType(): string
    {
        return $this->expectedType;
    }

    /**
     * Create exception for missing required option
     */
    public static function missingRequired(string $key): static
    {
        return new static(
            "Missing required configuration option: '{$key}'",
            $key,
            null,
            'required'
        );
    }

    /**
     * Create exception for invalid type
     */
    public static function invalidType(string $key, mixed $value, string $expectedType): static
    {
        $actualType = gettype($value);
        return new static(
            "Invalid type for configuration option '{$key}': expected {$expectedType}, got {$actualType}",
            $key,
            $value,
            $expectedType
        );
    }

    /**
     * Create exception for invalid value
     */
    public static function invalidValue(string $key, mixed $value, string $reason): static
    {
        return new static(
            "Invalid value for configuration option '{$key}': {$reason}",
            $key,
            $value
        );
    }

    /**
     * Create exception for value out of range
     */
    public static function outOfRange(string $key, mixed $value, mixed $min, mixed $max): static
    {
        return new static(
            "Configuration option '{$key}' value {$value} is out of range ({$min} - {$max})",
            $key,
            $value,
            "range [{$min}, {$max}]"
        );
    }

    /**
     * Create exception for invalid callback/closure
     */
    public static function invalidCallback(string $key): static
    {
        return new static(
            "Configuration option '{$key}' must be a valid callable",
            $key,
            null,
            'callable'
        );
    }
}
