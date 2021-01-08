<?php

namespace WizardsRest\ObjectManager;

use Doctrine\Persistence\ObjectManager;
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
     * @param array  $filterOperators
     *
     * @return QueryBuilder
     */
    private function findAllSorted(
        $className,
        array $sorting = [],
        array $filterValues = [],
        array $filterOperators = []
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
            return $repository->findAllSorted($sorting, $filterValues, $filterOperators);
        }

        $queryBuilder = $repository->createQueryBuilder('e');

        foreach ($sorting as $name => $direction) {
            if (in_array($name, $fields)) {
                $queryBuilder->addOrderBy('e.' . $name, $direction);
            }
        }

        foreach ($fields as $field) {
            $value = $this->getFilterValue($filterValues, $field);

            if (null !== $value) {
                $operator = $this->getFilterOperator($filterOperators, $field);
                $this->addFilter($queryBuilder, 'e', $field, $operator, $value);
            }

            $values = $this->getSubFiltersValues($filterValues, $field);

            if (count($values)) {
                $associationMapping = $metaData->getAssociationMapping($field);
                $relationMetaData = $this->objectManager->getClassMetadata($associationMapping['targetEntity']);
                $validatedValues = $this->getValidatedValues($relationMetaData, $values);
                $this->addSubFilters($queryBuilder, $field, $validatedValues, $filterOperators);
            }
        }

        return $queryBuilder;
    }

    /**
     * @param mixed $metaData
     * @param array $values
     *
     * @return array
    */
    private function getValidatedValues($metaData, $values)
    {
        $fields = array_keys($metaData->getReflectionProperties());
        $validatedValues = [];

        foreach ($fields as $field) {
            if (isset($values[$field])) {
                $validatedValues[$field] = $values[$field];
            }
        }

        return $validatedValues;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $field
     * @param array $values
     * @param array $filterOperators
     */
    private function addSubFilters($queryBuilder, $field, $values, $filterOperators)
    {
        if (count($values)) {
            $queryBuilder->innerJoin('e.' . $field, $field);

            foreach ($values as $subFieldName => $subFieldValue) {
                $operator = $this->getFilterOperator($filterOperators, $field . '.' . $subFieldName);
                $this->addFilter($queryBuilder, $field, $subFieldName, $operator, $subFieldValue);
            }
        }
    }

    /**
     * @param array $filterValues
     * @param string $field
     *
     * @return array
     */
    private function getSubFiltersValues(array $filterValues, string $field)
    {
        $subFiltersValues = [];
        foreach ($filterValues as $filterName => $filterValue) {
            $subsets = explode('.', $filterName);
            if ($field == $subsets[0] && isset($subsets[1])) {
                $subFiltersValues[$subsets[1]] = $filterValue;
            }
        }
        return $subFiltersValues;
    }

    /**
     * @param array $filterValues
     * @param string $field
     *
     * @return string|null
     */
    private function getFilterValue(array $filterValues, string $field)
    {
        if (isset($filterValues[$field])) {
            return $filterValues[$field];
        }
        return null;
    }

    /**
     * Creates a new where filter for your current querybuilder, based on the operator type
     */
    private function addFilter(QueryBuilder $queryBuilder, string $parent, string $field, string $operator, string $value)
    {
        if ('in' === $operator) {
            $queryBuilder->andWhere($queryBuilder->expr()->in(sprintf("%s.%s", $parent, $field), explode(',', $value)));

            return;
        }

        $queryBuilder->andWhere(sprintf("%s.%s%s'%s'", $parent, $field, $operator, $value));
    }

    /**
     * @param array $filterOperators
     * @param string $field
     *
     * @return string
     */
    private function getFilterOperator(array $filterOperators, string $field)
    {
        $allowedFilters = ['>', '<', '>=', '<=', '=', '!=', 'in'];
        if (isset($filterOperators[$field]) && in_array($filterOperators[$field], $allowedFilters)) {
            return $filterOperators[$field];
        }

        return '=';
    }
}
