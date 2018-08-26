<?php

namespace WizardsRest\ObjectManager;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\QueryBuilder;
use League\Fractal\Pagination\PagerfantaPaginatorAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\RouterInterface;
use WizardsRest\Parser\RestQueryParser;

/**
 * doctrine orm object manager
 * used to fetch & paginate collections according to the request
 *
 * @author Romain Richard
 */
class DoctrineOrmObjectManager implements ObjectManagerInterface
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Pagerfanta
     */
    private $paginator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * DoctrineOrmObjectManager constructor.
     * @param ObjectManager $objectManager
     * @param RouterInterface $router
     */
    public function __construct(ObjectManager $objectManager, RouterInterface $router)
    {
        $this->objectManager = $objectManager;
        $this->router = $router;
        $this->paginator = null;
    }

    /**
     * @param string $className
     * @param ServerRequestInterface $request
     *
     * @return array|\Traversable
     */
    public function getPaginatedCollection($className, ServerRequestInterface $request)
    {
        $parameters = new RestQueryParser($request);
        $doctrineAdapter = new DoctrineORMAdapter(
            $this->findAllSorted(
                $className,
                $this->parseSorting($parameters->get(RestQueryParser::PARAMETER_SORT)),
                $parameters->get(RestQueryParser::PARAMETER_FILTER),
                $parameters->get(RestQueryParser::PARAMETER_FILTER_OPERATOR)
            )
        );
        $this->paginator = new Pagerfanta($doctrineAdapter);
        $this->paginator->setMaxPerPage($parameters->get(RestQueryParser::PARAMETER_LIMIT));
        $this->paginator->setCurrentPage($parameters->get(RestQueryParser::PARAMETER_PAGE));

        return $this->paginator->getCurrentPageResults();
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return PagerfantaPaginatorAdapter|mixed
     */
    public function getPaginationAdapter(ServerRequestInterface $request)
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

    /**
     * @param string $className
     * @param array  $sorting
     * @param array  $filterValues
     * @param array  $filerOperators
     *
     * @return QueryBuilder
     */
    private function findAllSorted($className, array $sorting = [], array $filterValues = [], array $filerOperators = [])
    {
        $fields = array_keys($this->objectManager->getClassMetadata($className)->fieldMappings);
        $repository = $this->objectManager->getRepository($className);

        // If user's own implementation is defined, use it
        try {
            return $repository->findAllSorted($sorting, $filterValues, $filerOperators);
        } catch (\BadMethodCallException $exception) {
            $queryBuilder = $repository->createQueryBuilder('e');

            foreach ($sorting as $name => $direction) {
                if (in_array($name, $fields)) {
                    $queryBuilder->addOrderBy('e.' . $name, $direction);
                }
            }

            foreach ($fields as $field) {
                if (isset($filterValues[$field])) {
                    $operator = '=';

                    if (isset($filerOperators[$field])
                        && in_array($filerOperators[$field], ['>', '<', '>=', '<=', '=', '!='])
                    ) {
                        $operator = $filerOperators[$field];
                    }

                    $queryBuilder->andWhere('e.'.$field.$operator."'".$filterValues[$field]."'");
                }
            }

            return $queryBuilder;
        }
    }

    /**
     * Parse a jsonapi formatted sorting string to an array.
     * @param string $sort
     * @return array
     */
    private function parseSorting($sort)
    {
        $parsed = [];

        if ($sort) {
            $nameList = explode(',', $sort);
            foreach ($nameList as $name) {
                if ('-' === $name[0]) {
                    $parsed[substr($name, 1, strlen($name))] = 'desc';
                    continue;
                }

                $parsed[$name] = 'asc';
            }
        }

        return $parsed;
    }
}
