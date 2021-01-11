<?php

namespace WizardsRest\Exception;

/**
 * A simple HttpException.
 * The whole symfony or laravel http was kinda heavy to include.
 *
 * @author Romain Richard
 */
class HttpException extends \RuntimeException
{
    /**
     * @var int
     */
    private $statusCode;

    public function __construct(int $statusCode = 500, ?string $message = null)
    {
        $this->statusCode = $statusCode;

        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
