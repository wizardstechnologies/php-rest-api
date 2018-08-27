<?php

namespace WizardsRest;

use WizardsRest\ObjectManager\DoctrineOrmObjectManager;
use WizardsRest\ObjectManager\ObjectManagerInterface;
use Psr\Http\Message\ServerRequestInterface;
use League\Fractal\Manager;
use League\Fractal\Pagination\PaginatorInterface as FractalPaginatorInterface;
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
     * @var Manager
     */
    private $paginator;

    /**
     * @var DoctrineOrmObjectManager
     */
    private $objectManager;

    public function __construct(ObjectManagerInterface $objectManager, PaginatorInterface $paginator)
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

    /**
     * Get the fractal pagination adapter based on your object manager and your paginator.
     */
    public function getPaginationAdapter(ServerRequestInterface $request): FractalPaginatorInterface
    {
        return $this->paginator->getPaginationAdapter($request);
    }
}
