<?php

namespace WizardsRest\ObjectReader;

use WizardsRest\Annotation\Embeddable;
use WizardsRest\Annotation\Exposable;
use Doctrine\Common\Annotations\Reader;

/**
 * Reads an object configuration from an array
 *
 * @author Romain Richard
 */
class ArrayReader implements ObjectReaderInterface
{
    /**
     * There is no way to guess the name of the resource yet.
     */
    public function getResourceName($resource)
    {
        return 'coucou';
    }

    /**
     * It is not yet possible to embed relationships in arrays.
     */
    public function isPropertyEmbeddable(\ReflectionProperty $property)
    {
        return false;
    }

    /**
     * In an array, every property is exposed.
     */
    public function isPropertyExposable(\ReflectionProperty $property)
    {
        return true;
    }

    /**
     * In an array, every property is exposed
     */
    public function getExposedProperties($resource, array $filter)
    {
        return array_keys($resource);
    }

    public function getPropertyValue($resource, string $name)
    {
        return $resource[$name];
    }
}
