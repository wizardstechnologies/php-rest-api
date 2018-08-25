<?php

namespace WizardsRest\Exception;

use WizardsRest\Exception\BadRequestHttpException;

class IncludeNotFoundException extends BadRequestHttpException
{
    public function __construct()
    {
        parent::__construct('The property selected for inclusion is not available');
    }
}