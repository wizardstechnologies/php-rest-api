<?php

namespace App\ObjectReader;

/**
 * Reads annotations.
 *
 * @author Romain Richard
 */
interface ObjectReaderInterface
{
    /**
     * Return the configured route name for an embeddable relation.
     *
     * @param \ReflectionProperty $property
     * @param string              $targetClass
     *
     * @return string
     */
    public function getAssociationRouteName(\ReflectionProperty $property, $targetClass);

    /**
     * Return the configured route name for a resource, or get_*entityShortName* by default.
     *
     * @param \ReflectionClass $resource
     *
     * @return string
     */
    public function getResourceRouteName(\ReflectionClass $resource);

    /**
     * Return the configured route name for a resource collection, or get_*entityShortName*s by default.
     *
     * @param \ReflectionClass $resource
     *
     * @return string
     */
    public function getResourceCollectionRouteName(\ReflectionClass $resource);

    /**
     * Does an entity's property have the @embeddable annotation ?
     *
     * @param \ReflectionProperty $property
     *
     * @return bool
     */
    public function isPropertyEmbeddable(\ReflectionProperty $property);

    public function isPropertyExposable(\ReflectionProperty $property);

    public function getExposedProperties($resource, array $filter);

    public function getPropertyValue($resource, string $name);
}
