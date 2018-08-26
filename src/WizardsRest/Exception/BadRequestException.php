<?php

namespace WizardsRest\Exception;

/**
 * Class BadRequestException
 *
 * @package WizardsRest\Exception
 *
 * @author Romain Richard
 */
class BadRequestException extends HttpException
{
    public function __construct($message = 'Invalid Request')
    {
        parent::__construct(400, $message);
    }
}
