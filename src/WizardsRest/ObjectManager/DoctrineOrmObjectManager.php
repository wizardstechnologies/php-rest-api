<?php

namespace App\ObjectManager;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\QueryBuilder;
use League\Fractal\Pagination\PagerfantaPaginatorAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Routing\RouterInterface;

/**
 * Interface ObjectManagerInterface.
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
        $this->paninator = null;
    }

    /**
     * @param $className
     * @param $request
     *
     * @return array|mixed|\Traversable
     */
    public function getPaginatedCollection($className, $request)
    {
        $doctrineAdapter = new DoctrineORMAdapter(
            $this->findAllSorted(
                $className,
                $this->parseSorting($request->query->get('sort', '')),
                $request->query->get('filter', []),
                $request->query->get('filteroperator', [])
            )
        );
        $this->paginator = new Pagerfanta($doctrineAdapter);
        $this->paginator->setMaxPerPage($request->query->get('limit', 10));
        $this->paginator->setCurrentPage($request->query->get('page', 1));

        return $this->paginator->getCurrentPageResults();
    }

    /**
     * @param $request
     * @return PagerfantaPaginatorAdapter|mixed
     */
    public function getPaginationAdapter($request)
    {
        $router = $this->router;
        return new PagerfantaPaginatorAdapter(
            $this->paginator,
            function(int $page) use ($request, $router) {
                $route = $request->attributes->get('_route');
                $inputParams = $request->attributes->get('_route_params');
                $newParams = array_merge($inputParams, $request->query->all());
                $newParams['page'] = $page;
                return $router->generate($route, $newParams);
            });
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