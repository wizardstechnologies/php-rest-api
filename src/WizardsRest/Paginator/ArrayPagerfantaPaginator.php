<?php

namespace WizardsRest\Paginator;

use Doctrine\Common\Collections\Collection;
use League\Fractal\Pagination\PagerfantaPaginatorAdapter;
use League\Fractal\Pagination\PaginatorInterface as FractalPaginatorInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\RouterInterface;
use WizardsRest\Parser\RestQueryParser;

/**
 * Paginate an array or an ArrayCollection using PagerFanta.
 */
class ArrayPagerfantaPaginator implements PaginatorInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var Pagerfanta
     */
    private $paginator;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    private function getPaginator($collection, ServerRequestInterface $request)
    {
        if ($collection instanceof Collection) {
            $collection = $collection->toArray();
        }

        $parameters = new RestQueryParser($request);
        $adapter = new ArrayAdapter($collection);

        $this->paginator = new Pagerfanta($adapter);
        $this->paginator->setMaxPerPage($parameters->get(RestQueryParser::PARAMETER_LIMIT));
        $this->paginator->setCurrentPage($parameters->get(RestQueryParser::PARAMETER_PAGE));

        return $this->paginator;
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
            $this->paginator,
            function (int $page) use ($request, $attributes, $router) {
                $route = $attributes['_route'];
                $inputParams = $attributes['_route_params'];
                $newParams = array_merge($inputParams, $request->getQueryParams());
                $newParams['page'] = $page;

                return $router->generate($route, $newParams);
            }
        );
    }
}
