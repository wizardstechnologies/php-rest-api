<?php

namespace WizardsRest\ObjectReader;

/**
 * Tells us about an object exposition.
 *
 * @author Romain Richard
 */
interface ObjectReaderInterface
{
    /**
     * Can the given property be exposed ?
     *
     * @param \ReflectionProperty $property
     *
     * @return mixed
     */
    public function isPropertyExposable(\ReflectionProperty $property);

    /**
     * Cant the given property be embedded/included ?
     *
     * @param \ReflectionProperty $property
     *
     * @return mixed
     */
    public function isPropertyEmbeddable(\ReflectionProperty $property);

    /**
     * Get the list of properties than can be exposed.
     *
     * @param $resource
     * @param array $filter
     *
     * @return mixed
     */
    public function getExposedProperties($resource, array $filter);

    /**
     * Get the value of an object's property.
     * @param $resource
     * @param string $name
     *
     * @return mixed
     */
    public function getPropertyValue($resource, string $name);

    public function getResourceName($resource);
}
