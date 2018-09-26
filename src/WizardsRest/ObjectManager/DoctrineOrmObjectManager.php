<?php

namespace WizardsRest\ObjectManager;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\QueryBuilder;
use League\Fractal\Pagination\PagerfantaPaginatorAdapter;
use League\Fractal\Pagination\PaginatorInterface;
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


    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function fetchCollection(string $className, ServerRequestInterface $request)
    {
        $parameters = new RestQueryParser($request);

        return $this->findAllSorted(
            $className,
            $parameters->getParsedSorting(),
            $parameters->get(RestQueryParser::PARAMETER_FILTER),
            $parameters->get(RestQueryParser::PARAMETER_FILTER_OPERATOR)
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

            // @TODO: multiple sub filters on fields like
            // ?filers[cards.amount]=100&filter[cards.userdId]=1&fileroperatos[cards.amount]=>=
            // right now we can only filter on sub resources by id
            foreach ($fields as $field => $reflection) {
                $value = $this->getFilterValue($filterValues, $field);
                if (null !== $value) {
                    $operator = $this->getFilterOperator($filerOperators, $field);
                    $field = $this->getFilterField($filterValues, $field);
                    $queryBuilder->andWhere(sprintf("e.%s%s'%s'", $field, $operator, $value));
                }
            }

            return $queryBuilder;
        }
    }

    /**
     * @param $filterValues
     * @param $field
     * @return string|null
     */
    private function getFilterValue($filterValues, $field)
    {
        foreach ($filterValues as $filterName => $filterValue) {
            $subsets = explode('.', $filterName);
            if ($field == $subsets[0]) {
                return $filterValue;
            }
        }

        return null;
    }

    private function getFilterField($filterValues, $field)
    {
        foreach (array_keys($filterValues) as $filterName) {
            $subsets = explode('.', $filterName);
            if ($field == $subsets[0]) {
                return $subsets[0];
            }
        }

        return null;
    }

    private function getFilterOperator($filerOperators, $field)
    {
        $allowedFilters = ['>', '<', '>=', '<=', '=', '!='];

        if (isset($filerOperators[$field]) && in_array($filerOperators[$field], $allowedFilters)) {
            return $filerOperators[$field];
        }

        return '=';
    }
}
