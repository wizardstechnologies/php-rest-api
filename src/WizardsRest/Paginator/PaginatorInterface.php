<?php

namespace WizardsRest\Paginator;

use League\Fractal\Pagination\PaginatorInterface as FractalPaginatorInterface;
use Psr\Http\Message\ServerRequestInterface;

interface PaginatorInterface
{
    public function paginate($collection, ServerRequestInterface $request);

    public function getPaginationAdapter($collection, ServerRequestInterface $request): FractalPaginatorInterface;
}
