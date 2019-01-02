<?php

namespace WizardsRest\Parser;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Parse the rest query parameters into a reliable array with defaults.
 * Those parameters are useful to manipulate collection, and are documented in the jsonapi specification.
 */
class RestQueryParser
{
    const PARAMETER_PAGE = 'page';
    const PARAMETER_LIMIT = 'limit';
    const PARAMETER_SORT = 'sort';
    const PARAMETER_FILTER = 'filter';
    /**
     * This is an extra filtering parameter, not documented in jsonapi, but really useful to filter api requests.
     * It supports operators such as <, >, <=, >=, !=
    */
    const PARAMETER_FILTER_OPERATOR = 'filteroperator';

    const DEFAULT_PAGE = 1;
    const DEFAULT_LIMIT = 20;
    const DEFAULT_SORT = null;
    const DEFAULT_FILTER = [];
    const DEFAULT_FILTER_OPERATOR = [];

    /**
     * @var array
     */
    private $parsedParameters;

    /**
     * RestQueryParser constructor.
     *
     * @param ServerRequestInterface $request
     */
    public function __construct(ServerRequestInterface $request)
    {
        $this->parsedParameters = $this->parseRequest($request);
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function get(string $name)
    {
        return $this->parsedParameters[$name] ?? null;
    }

    public function getParsedSorting()
    {
        $parsed = [];

        if ($this->get(self::PARAMETER_SORT)) {
            $nameList = explode(',', $this->get(self::PARAMETER_SORT));
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

    /**
     * @param ServerRequestInterface $request
     *
     * @return array
     */
    private function parseRequest(ServerRequestInterface $request)
    {
        $resolver = new OptionsResolver();

        $resolver->setDefaults([
            self::PARAMETER_PAGE => (string) self::DEFAULT_PAGE,
            self::PARAMETER_LIMIT => (string) self::DEFAULT_LIMIT,
            self::PARAMETER_SORT => self::DEFAULT_SORT,
            self::PARAMETER_FILTER => self::DEFAULT_FILTER,
            self::PARAMETER_FILTER_OPERATOR => self::DEFAULT_FILTER_OPERATOR,
        ]);

        $resolver->setAllowedTypes(self::PARAMETER_PAGE, ['NULL', 'string']);
        $resolver->setAllowedTypes(self::PARAMETER_LIMIT, ['NULL', 'string']);
        $resolver->setAllowedTypes(self::PARAMETER_SORT, ['NULL', 'string']);
        $resolver->setAllowedTypes(self::PARAMETER_FILTER, ['NULL', 'array']);
        $resolver->setAllowedTypes(self::PARAMETER_FILTER_OPERATOR, ['NULL', 'array']);

        $queryParams = $request->getQueryParams();

        return $resolver->resolve(array_filter([
            self::PARAMETER_PAGE => $queryParams[self::PARAMETER_PAGE] ?? '',
            self::PARAMETER_LIMIT => $queryParams[self::PARAMETER_LIMIT] ?? '',
            self::PARAMETER_SORT => $queryParams[self::PARAMETER_SORT] ?? '',
            self::PARAMETER_FILTER => $queryParams[self::PARAMETER_FILTER] ?? '',
            self::PARAMETER_FILTER_OPERATOR => $queryParams[self::PARAMETER_FILTER_OPERATOR] ?? '',
        ]));
    }
}
