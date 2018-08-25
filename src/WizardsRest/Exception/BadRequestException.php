<?php

namespace WizardsRest\Exception;

class BadRequestException extends HttpException
{
    public function __construct($message = 'Invalid Request')
    {
        parent::__construct(400, $message);
    }
}