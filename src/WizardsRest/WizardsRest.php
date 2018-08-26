<?php

namespace WizardsRest;

use WizardsRest\ObjectManager\DoctrineOrmObjectManager;
use WizardsRest\Transformer\EntityTransformer;
use Psr\Http\Message\ServerRequestInterface;
use League\Fractal;
use League\Fractal\Manager;
use League\Fractal\Serializer\JsonApiSerializer;
use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\Resource\ResourceInterface;

/**
 * A service to manage entity & collection transformation & serialization.
 * Flow:
 * $resource = $wizardsRest->transform($entityOrCollection, $request);
 * $serialized = $wizardsRest->serialize($resource, $specification, $format);
 *
 * @package WizardsRest
 *
 * @author Romain Richard
 */
class WizardsRest
{
    const SPEC_JSONAPI = 'SPEC_JSONAPI';
    const SPEC_ARRAY = 'SPEC_ARRAY';
    const SPEC_DATA_ARRAY = 'SPEC_DATA_ARRAY';

    const FORMAT_JSON = 'FORMAT_JSON';
    const FORMAT_ARRAY = 'FORMAT_ARRAY';

    /**
     * @var EntityTransformer
     */
    private $defaultTransformer;

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var DoctrineOrmObjectManager
     */
    private $objectManager;

    /**
     * WizardsRest constructor.
     *
     * @param EntityTransformer $defaultTransformer
     * @param DoctrineOrmObjectManager $objectManager
     */
    public function __construct(
        EntityTransformer $defaultTransformer,
        DoctrineOrmObjectManager $objectManager
    ) {
        $this->defaultTransformer = $defaultTransformer;
        $this->manager = new Manager();
        $this->objectManager = $objectManager;
    }

    /**
     * Transforms an entity or a collection in a Fractal Resource.
     *
     * @param $entity
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
        Fractal\TransformerAbstract $userTransformer = null
    ) {
        $transformer = null === $userTransformer ? $this->getDefaultTransformer($request) : $userTransformer;

        // @TODO: parse includes only if option is activated, which should be the case by default
        // also, this could be done at many other places, is here the best place to ?
        $this->manager->parseIncludes($this->getComaSeparatedQueryParams($request, 'include'));


        if (is_array($entity) || $entity instanceof \Traversable) {
            return new Fractal\Resource\Collection(
                $entity,
                $transformer,
                strtolower((new \ReflectionClass($entity[0]))->getShortName())
            );
        }

        return new Fractal\Resource\Item(
            $entity,
            $transformer,
            strtolower((new \ReflectionClass($entity))->getShortName())
        );
    }

    /**
     * Serialize a Fractal Resource to the given format & specification.
     *
     * @param ResourceInterface $resource
     * @param string $specification
     * @param string $format
     *
     * @return array|string
     */
    public function serialize(
        ResourceInterface $resource,
        $specification = self::SPEC_DATA_ARRAY,
        $format = self::FORMAT_ARRAY
    ) {
        switch ($specification) {
            case self::SPEC_JSONAPI:
                $baseUrl = 'http://example.com';
                $this->manager->setSerializer(new JsonApiSerializer($baseUrl));
                break;
            case self::SPEC_ARRAY:
                $this->manager->setSerializer(new ArraySerializer());
                break;
        }


        switch ($format) {
            case self::FORMAT_JSON:
                return $this->manager->createData($resource)->toJson();
        }

        return $this->manager->createData($resource)->toArray();
    }

    /**
     * Fetches a paginated collection from the object manager.
     *
     * @param $className
     * @param ServerRequestInterface $request
     *
     * @return array|\Traversable
     */
    public function getPaginatedCollection($className, ServerRequestInterface $request)
    {
        return $this->objectManager->getPaginatedCollection(
            $className,
            $request
        );
    }

    /**
     * Get the pagination adapter tailored for your object manager.
     *
     * @param $request
     *
     * @return Fractal\Pagination\PagerfantaPaginatorAdapter|mixed
     */
    public function getPaginationAdapter($request)
    {
        return $this->objectManager->getPaginationAdapter($request);
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
