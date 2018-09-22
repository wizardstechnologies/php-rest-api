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
    public function getResourceName($resource)
    {
        return 'coucou';
    }

    public function isPropertyEmbeddable(\ReflectionProperty $property)
    {
        return false;
    }

    public function isPropertyExposable(\ReflectionProperty $property)
    {
        return true;
    }

    public function getExposedProperties($resource, array $filter)
    {
        return array_keys($resource);
    }

    public function getPropertyValue($resource, string $name)
    {
        return $resource[$name];
    }
}
