<?php

namespace WizardsRest\ObjectManager;

use League\Fractal\Pagination\PaginatorInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface ObjectManagerInterface.
 *
 * @author Romain Richard
 */
interface ObjectManagerInterface
{
    /**
     * @param $className
     * @param ServerRequestInterface$request
     *
     * @return mixed
     */
    public function getPaginatedCollection($className, ServerRequestInterface $request);

    /**
     * @param ServerRequestInterface $request
     * @return PaginatorInterface|null
     */
    public function getPaginationAdapter(ServerRequestInterface $request);
}
