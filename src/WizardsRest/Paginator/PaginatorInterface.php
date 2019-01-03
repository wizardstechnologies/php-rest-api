<?php

namespace WizardsRest\Paginator;

use League\Fractal\Pagination\PaginatorInterface as FractalPaginatorInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Paginate a collection of resources.
 * The Collection could be anything, as it is the job of the implementation to be able to select a slice from it.
 *
 * @package WizardsRest\Paginator
 */
interface PaginatorInterface
{
    /**
     * Get a slice of the results according to an http request.
     *
     * @param \Traversable $collection
     * @param ServerRequestInterface $request
     *
     * @return mixed
     */
    public function paginate($collection, ServerRequestInterface $request);

    /**
     * Get the informations on the pagination (current page, total results...) according to the current collection
     * and an http request.
     *
     * @param \Traversable $collection
     * @param ServerRequestInterface $request
     *
     * @return FractalPaginatorInterface
     */
    public function getPaginationAdapter($collection, ServerRequestInterface $request): FractalPaginatorInterface;
}
