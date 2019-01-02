<?php

namespace WizardsRest;

use WizardsRest\ObjectManager\ObjectManagerInterface;
use Psr\Http\Message\ServerRequestInterface;
use WizardsRest\Paginator\PaginatorInterface;

/**
 * A service to help you fetch a collection according to query params
 *
 * @package WizardsRest
 *
 * @author Romain Richard
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
     * CollectionManager constructor.
     *
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
     */
    public function getPaginatedCollection(string $className, ServerRequestInterface $request): \Traversable
    {
        return $this->paginator->paginate($this->objectManager->fetchCollection($className, $request), $request);
    }
}
