<?php

namespace WizardsRest;

use App\ObjectManager\DoctrineOrmObjectManager;
use App\Transformer\EntityTransformer;
use Symfony\Component\HttpFoundation\Request;
use League\Fractal;
use League\Fractal\Manager;
use League\Fractal\Serializer\JsonApiSerializer;
use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\Resource\ResourceInterface;

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

    public function __construct(
        EntityTransformer $defaultTransformer,
        DoctrineOrmObjectManager $objectManager
    )
    {
        $this->defaultTransformer = $defaultTransformer;
        $this->manager = new Manager();
        $this->objectManager = $objectManager;
    }

    public function transform($entity, Request $request, Fractal\TransformerAbstract $userTransformer = null)
    {
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

    public function getPaginatedCollection($className, Request $request)
    {
        return $this->objectManager->getPaginatedCollection(
            $className,
            $request
        );
    }

    public function getPaginationAdapter($request)
    {
        return $this->objectManager->getPaginationAdapter($request);
    }

    private function getDefaultTransformer(Request $request)
    {
        $this->defaultTransformer->setAvailableIncludes($this->getComaSeparatedQueryParams($request, 'include'));
        $this->defaultTransformer->setAvailableFields($this->getComaSeparatedQueryParams($request, 'fields'));

        return $this->defaultTransformer;
    }

    /**
     * Get the embed query param.
     *
     * @return array
     */
    private function getComaSeparatedQueryParams(Request $request, $name)
    {
        $include = $request->query->get($name);

        return $include ? explode(',', $include) : [];
    }
}