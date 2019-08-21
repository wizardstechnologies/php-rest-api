<?php

namespace WizardsRest\ObjectManager;

use Psr\Http\Message\ServerRequestInterface;
use WizardsRest\Parser\RestQueryParser;

/**
 * doctrine orm object manager
 * used to fetch & paginate collections according to the request
 *
 * @author Romain Richard
 */
class ArrayObjectManager implements ObjectManagerInterface
{
    /**
     * Get the actual collection of the request for a given source.
     *
     * @param array                  $source
     * @param ServerRequestInterface $request
     *
     * @return array
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
     * @param array $source
     * @param array  $sorting
     * @param array  $filterValues
     * @param array  $filerOperators
     *
     * @return array
     */
    private function findAllSorted(
        $source,
        array $sorting = [],
        array $filterValues = [],
        array $filerOperators = []
    ) {
        $result = $source;

        // filter out results
        if ($filterValues) {
            $result = array_filter($source, function ($resource) use ($filterValues, $filerOperators) {
                return $this->filterResults($resource, $filterValues, $filerOperators);
            });
        }

        // sort out results
        if ($sorting) {
            foreach ($sorting as $sortField => $sortWay) {
                usort($result, function ($previous, $next) use ($sortField, $sortWay) {
                    if ('asc' === $sortWay) {
                        return strcmp($previous[$sortField], $next[$sortField]);
                    }

                    return strcmp($next[$sortField], $previous[$sortField]);
                });
            }
        }

        // clean the array after filtering and sorting
        $finalResult = [];
        foreach ($result as $value) {
            $finalResult[] = $value;
        }

        return $finalResult;
    }

    private function filterResults($resource, $filterValues, $filerOperators)
    {
        foreach ($resource as $fieldName => $fieldValue) {
            if (isset($filterValues[$fieldName])) {
                if (isset($filerOperators[$fieldName])) {
                    switch ($filerOperators[$fieldName]) {
                        case '>':
                            return $fieldValue > $filterValues[$fieldName];
                        case '<':
                            return $fieldValue < $filterValues[$fieldName];
                        case '>=':
                            return $fieldValue >= $filterValues[$fieldName];
                        case '<=':
                            return $fieldValue <= $filterValues[$fieldName];
                        case '!=':
                            return $fieldValue != $filterValues[$fieldName];
                    }
                }

                return $fieldValue == $filterValues[$fieldName];
            }
        }

        return false;
    }
}
