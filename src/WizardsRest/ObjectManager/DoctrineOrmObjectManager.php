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
}
