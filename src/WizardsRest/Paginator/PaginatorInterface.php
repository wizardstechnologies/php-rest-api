<?php

namespace WizardsRest\Paginator;

use League\Fractal\Pagination\PaginatorInterface as FractalPaginatorInterface;
use Psr\Http\Message\ServerRequestInterface;

interface PaginatorInterface
{
    public function paginate($collection, $request);

    public function getPaginationAdapter(ServerRequestInterface $request): FractalPaginatorInterface;
}
