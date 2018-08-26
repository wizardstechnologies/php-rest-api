<?php

namespace WizardsRest\Exception;

/**
 * HttpException.
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

    /**
     * HttpException constructor.
     *
     * @param int $statusCode
     * @param string|null $message
     */
    public function __construct(int $statusCode = 500, string $message = null)
    {
        $this->statusCode = $statusCode;

        parent::__construct($message);
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
}
