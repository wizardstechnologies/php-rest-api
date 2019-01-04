<?php

namespace WizardsRest\ObjectManager;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\QueryBuilder;
use Psr\Http\Message\ServerRequestInterface;
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

    /**
     * Get the actual collection of the request for a given source.
     *
     * @param string                 $source The class name of the entity.
     * @param ServerRequestInterface $request
     *
     * @return mixed
     */
    public function fetchCollection($source, ServerRequestInterface $request)
    {
        $parameters = new RestQueryParser($request);

        return $this->findAllSorted(
            $source,
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
    private function findAllSorted(
        $className,
        array $sorting = [],
        array $filterValues = [],
        array $filerOperators = []
    ) {
        /**
         * @var ClassMetadataInfo $metaData
         */
        $metaData = $this->objectManager->getClassMetadata($className);
        $fields = array_keys($metaData->getReflectionProperties());

        /**
         * @var EntityRepository
         */
        $repository = $this->objectManager->getRepository($className);

        // If user's own implementation is defined, use it
        if (method_exists($repository, 'findAllSorted')) {
            return $repository->findAllSorted($sorting, $filterValues, $filerOperators);
        }

        $queryBuilder = $repository->createQueryBuilder('e');

        foreach ($sorting as $name => $direction) {
            if (in_array($name, $fields)) {
                $queryBuilder->addOrderBy('e.' . $name, $direction);
            }
        }

        // @TODO: multiple sub filters on fields like
        // ?filers[cards.amount]=100&filter[cards.userId]=1&fileroperator[cards.amount]=>=
        // right now we can only filter on sub resources by id
        foreach ($fields as $field) {
            $value = $this->getFilterValue($filterValues, $field);
            if (null !== $value) {
                $operator = $this->getFilterOperator($filerOperators, $field);
                $field = $this->getFilterField($filterValues, $field);
                $queryBuilder->andWhere(sprintf("e.%s%s'%s'", $field, $operator, $value));
            }
        }

        return $queryBuilder;
    }

    /**
     * @param array $filterValues
     * @param string $field
     *
     * @return string|null
     */
    private function getFilterValue(array $filterValues, string $field)
    {
        foreach ($filterValues as $filterName => $filterValue) {
            $subsets = explode('.', $filterName);
            if ($field == $subsets[0]) {
                return $filterValue;
            }
        }

        return null;
    }

    /**
     * @param array $filterValues
     * @param string $field
     *
     * @return string|null
     */
    private function getFilterField(array $filterValues, string $field)
    {
        foreach (array_keys($filterValues) as $filterName) {
            $subsets = explode('.', $filterName);
            if ($field == $subsets[0]) {
                return $subsets[0];
            }
        }

        return null;
    }

    /**
     * @param array $filerOperators
     * @param string $field
     *
     * @return string
     */
    private function getFilterOperator(array $filerOperators, string $field)
    {
        $allowedFilters = ['>', '<', '>=', '<=', '=', '!='];

        if (isset($filerOperators[$field]) && in_array($filerOperators[$field], $allowedFilters)) {
            return $filerOperators[$field];
        }

        return '=';
    }
}
