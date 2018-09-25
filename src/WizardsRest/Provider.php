<?php

namespace WizardsRest;

use WizardsRest\Transformer\EntityTransformer;
use Psr\Http\Message\ServerRequestInterface;
use League\Fractal;
use League\Fractal\Manager;
use League\Fractal\Resource\ResourceAbstract;

/**
 * A service to help you abstract an entity or a collection with fractal
 *
 * @package WizardsRest
 *
 * @author Romain Richard
 */
class Provider
{
    /**
     * @var EntityTransformer
     */
    private $defaultTransformer;

    /**
     * @var Manager
     */
    private $manager;

    public function __construct(EntityTransformer $defaultTransformer, Manager $manager)
    {
        $this->defaultTransformer = $defaultTransformer;
        $this->manager = $manager;
    }

    /**
     * Transforms an entity or a collection in a Fractal Resource.
     *
     * @param object $entity
     * @param ServerRequestInterface $request
     * @param Fractal\TransformerAbstract|null $userTransformer
     *
     * @return Fractal\Resource\Collection|Fractal\Resource\Item
     *
     * @throws \ReflectionException
     */
    public function transform(
        $entity,
        ServerRequestInterface $request,
        $userTransformer = null,
        string $name = null
    ): ResourceAbstract {
        $this->defaultTransformer->setAvailableIncludes($this->getComaSeparatedQueryParams($request, 'include'));
        $this->defaultTransformer->setAvailableFields($this->getComaSeparatedQueryParams($request, 'fields'));
        $this->manager->parseIncludes($this->getComaSeparatedQueryParams($request, 'include'));

        $transformer = null === $userTransformer ? $this->getDefaultTransformer($request) : $userTransformer;

        if (is_array($entity) || $entity instanceof \Traversable) {
            return new Fractal\Resource\Collection(
                $entity,
                $transformer,
                $name ?? strtolower((new \ReflectionClass($entity[0]))->getShortName())
            );
        }

        return new Fractal\Resource\Item(
            $entity,
            $transformer,
            $name ?? strtolower((new \ReflectionClass($entity))->getShortName())
        );
    }

    /**
     * Get the default transformer which is a environment agnostic entity transformer
     *
     * @param ServerRequestInterface $request
     *
     * @return EntityTransformer
     */
    private function getDefaultTransformer(ServerRequestInterface $request)
    {
        $this->defaultTransformer->setAvailableIncludes($this->getComaSeparatedQueryParams($request, 'include'));
        $this->defaultTransformer->setAvailableFields($this->getComaSeparatedQueryParams($request, 'fields'));

        return $this->defaultTransformer;
    }

    /**
     * Get the exploded value of a comma-separated query param
     *
     * @param ServerRequestInterface $request
     * @param string $name
     *
     * @return array
     */
    private function getComaSeparatedQueryParams(ServerRequestInterface $request, string $name)
    {
        $queryParams = $request->getQueryParams();

        return isset($queryParams[$name]) ? explode(',', $queryParams[$name]) : [];
    }
}
