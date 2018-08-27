<?php

namespace WizardsRest\ObjectManager;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface ObjectManagerInterface.
 *
 * @author Romain Richard
 */
interface ObjectManagerInterface
{
    /**
     * Get the actual collection of the request for a className
     */
    public function fetchCollection(string $className, ServerRequestInterface $request);
}
