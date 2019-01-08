<?php

namespace WizardsRest;

use WizardsRest\ObjectManager\ObjectManagerInterface;
use Psr\Http\Message\ServerRequestInterface;
use WizardsRest\Paginator\PaginatorInterface;

/**
 * A service to help you fetch a collection according to query params
 */
class CollectionManager
{
    /**
     * @var PaginatorInterface
     */
    private $paginator;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param PaginatorInterface $paginator
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(PaginatorInterface $paginator, ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
        $this->paginator = $paginator;
    }

    /**
     * Fetches a paginated collection from the object manager.
     * Don't use it in combination with wizards-rest-bundle as you would do the pagination twice.
     * Prefer getFilteredCollection.
     *
     * @param string $className
     * @param ServerRequestInterface $request
     *
     * @return \Traversable
     */
    public function getPaginatedCollection(string $className, ServerRequestInterface $request): \Traversable
    {
        return $this->paginator->paginate($this->objectManager->fetchCollection($className, $request), $request);
    }

    /**
     * @param mixed                  $source
     * @param ServerRequestInterface $request
     *
     * @return \Traversable|array
     */
    public function getFilteredCollection($source, ServerRequestInterface $request)
    {
        return $this->objectManager->fetchCollection($source, $request);
    }
}
