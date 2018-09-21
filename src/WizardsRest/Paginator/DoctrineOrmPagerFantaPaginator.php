<?php

namespace WizardsRest\Paginator;

use League\Fractal\Pagination\PagerfantaPaginatorAdapter;
use League\Fractal\Pagination\PaginatorInterface as FractalPaginatorInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\RouterInterface;
use WizardsRest\Parser\RestQueryParser;

class DoctrineOrmPagerFantaPaginator implements PaginatorInterface
{
    /**
     * @var Pagerfanta
     */
    private $paginator;

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function paginate($collection, ServerRequestInterface $request)
    {
        return $this->getPaginator($collection, $request)->getCurrentPageResults();
    }

    public function getPaginationAdapter($collection, ServerRequestInterface $request): FractalPaginatorInterface
    {
        $router = $this->router;
        $attributes = $request->getAttributes();

        return new PagerfantaPaginatorAdapter(
            $this->getPaginator($collection, $request),
            function (int $page) use ($request, $attributes, $router) {
                $route = $attributes['_route'];
                $inputParams = $attributes['_route_params'];
                $newParams = array_merge($inputParams, $request->getQueryParams());
                $newParams['page'] = $page;

                return $router->generate($route, $newParams);
            }
        );
    }

    private function getPaginator($collection, ServerRequestInterface $request)
    {
        if ($this->paginator) {
            return $this->paginator;
        }

        $parameters = new RestQueryParser($request);
        $doctrineAdapter = new DoctrineORMAdapter($collection);
        $this->paginator = new Pagerfanta($doctrineAdapter);
        $this->paginator->setMaxPerPage($parameters->get(RestQueryParser::PARAMETER_LIMIT));
        $this->paginator->setCurrentPage($parameters->get(RestQueryParser::PARAMETER_PAGE));

        return $this->paginator;
    }
}
