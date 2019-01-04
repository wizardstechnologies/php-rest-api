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
     * Get the actual collection of the request for a given source.
     *
     * @param mixed                  $source
     * @param ServerRequestInterface $request
     *
     * @return mixed
     */
    public function fetchCollection($source, ServerRequestInterface $request);
}
