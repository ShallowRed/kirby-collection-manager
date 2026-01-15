<?php

declare(strict_types=1);

namespace KirbyCollectionManager\Exception;

use Exception;
use Throwable;

/**
 * Base exception for Collection Manager errors
 *
 * Provides structured error handling with context and error codes
 * for better debugging and logging.
 */
class CollectionException extends Exception
{
    /**
     * Additional context data for debugging
     */
    protected array $context = [];

    /**
     * Create a new collection exception
     *
     * @param string $message The exception message
     * @param int $code The exception code
     * @param Throwable|null $previous The previous exception for chaining
     * @param array $context Additional context data
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Get additional context data
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Create exception with context
     */
    public static function withContext(string $message, array $context = [], int $code = 0): static
    {
        return new static($message, $code, null, $context);
    }

    /**
     * Get a formatted error message including context
     */
    public function getDetailedMessage(): string
    {
        $message = $this->getMessage();

        if (!empty($this->context)) {
            $contextStr = json_encode($this->context, JSON_PRETTY_PRINT);
            $message .= "\n\nContext:\n{$contextStr}";
        }

        return $message;
    }
}
