<?php

namespace WizardsRest\Transformer;

use WizardsRest\Exception\IncludeNotFoundException;
use WizardsRest\ObjectReader\ObjectReaderInterface;
use League\Fractal\TransformerAbstract;

/**
 * A generic Fractal Transformer to transform an entity/document to a Fractal Resource.
 *
 * @author Romain Richard
 */
class EntityTransformer extends TransformerAbstract
{
    /**
     * @var ObjectReaderInterface
     */
    private $objectReader;

    /**
     * List of fields possible to display
     *
     * @var array
     */
    private $availableFields = [];

    /**
     * EntityTransformer constructor.
     * It needs an objectReader to know which fields to expose.
     *
     * @param ObjectReaderInterface $objectReader
     */
    public function __construct(ObjectReaderInterface $objectReader)
    {
        $this->objectReader = $objectReader;
    }

    /**
     * @param array $includes
     */
    public function setAvailableIncludes($includes)
    {
        $this->availableIncludes = $includes;
    }

    public function setAvailableFields(array $fields)
    {
        $this->availableFields = $fields;
    }

    /**
     * Transform an object to an array thanks to the object reader.
     *
     * @param object $resource An entity/document to expose
     *
     * @return mixed
     */
    public function transform($resource)
    {
        return $this->objectReader->getExposedProperties($resource, $this->availableFields);
    }

    /**
     * Dynamic inclusion of included/embedded params.
     *
     * @param string $name
     * @param array $arguments
     *
     * @return \League\Fractal\Resource\Collection|\League\Fractal\Resource\Item
     */
    public function __call(string $name, array $arguments)
    {
        if (0 === strpos($name, 'include') && strlen($name) > strlen('include')) {
            try {
                return $this->includeResource(
                    $arguments[0],
                    substr($name, strlen('include'), strlen($name))
                );
            } catch (\Exception $exception) {
                throw new IncludeNotFoundException();
            }
        }
    }

    /**
     * Get the include value.
     *
     * @param object $entity
     * @param string $name
     *
     * @return \League\Fractal\Resource\Collection|\League\Fractal\Resource\Item
     */
    private function includeResource($entity, $name)
    {
        $resource = $this->objectReader->getPropertyValue($entity, $name);

        if (is_array($resource) || $resource instanceof \Traversable) {
            return $this->collection($resource, $this, $this->objectReader->getResourceName($resource));
        }

        return $this->item($resource, $this, $this->objectReader->getResourceName($resource));
    }
}
