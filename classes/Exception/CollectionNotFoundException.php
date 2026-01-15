<?php

declare(strict_types=1);

namespace KirbyCollectionManager\Exception;

/**
 * Exception thrown when collection operations fail
 *
 * This exception is thrown when:
 * - Collection source is not found or invalid
 * - Collection processing fails (filtering, sorting, etc.)
 * - Pagination errors occur
 */
class CollectionNotFoundException extends CollectionException
{
    /**
     * The collection source that was not found
     */
    protected string $source = '';

    /**
     * Create a new collection not found exception
     */
    public function __construct(
        string $message = '',
        string $source = '',
        int $code = 0
    ) {
        $context = [];

        if ($source !== '') {
            $context['source'] = $source;
            $this->source = $source;
        }

        parent::__construct($message, $code, null, $context);
    }

    /**
     * Get the collection source
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * Create exception for invalid collection type
     */
    public static function invalidType(string $source, string $actualType): static
    {
        return new static(
            "Collection source '{$source}' returned invalid type: {$actualType}. Expected Pages, Files, or Users collection.",
            $source
        );
    }

    /**
     * Create exception for undefined collection source
     */
    public static function undefinedSource(string $source): static
    {
        return new static(
            "Collection source '{$source}' is not defined. Check your configuration.",
            $source
        );
    }

    /**
     * Create exception for empty collection
     */
    public static function emptyCollection(string $source): static
    {
        return new static(
            "Collection source '{$source}' returned an empty collection.",
            $source
        );
    }
}
