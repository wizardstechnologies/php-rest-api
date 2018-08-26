<?php

namespace WizardsRest\Exception;

/**
 * Class IncludeNotFoundException
 *
 * @package WizardsRest\Exception
 *
 * @author Romain Richard
 */
class IncludeNotFoundException extends BadRequestException
{
    public function __construct()
    {
        parent::__construct('The property selected for inclusion is not available');
    }
}
