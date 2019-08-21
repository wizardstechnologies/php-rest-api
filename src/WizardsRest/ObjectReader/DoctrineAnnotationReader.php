<?php

namespace WizardsRest\ObjectReader;

use Doctrine\Common\Util\ClassUtils;
use WizardsRest\Annotation\Embeddable;
use WizardsRest\Annotation\Exposable;
use Doctrine\Common\Annotations\Reader;
use WizardsRest\Annotation\Type;
use Doctrine\ORM\Proxy\Proxy;
use \ReflectionClass;
use \ReflectionProperty;

/**
 * Reads an object configuration from annotations.
 *
 * @author Romain Richard
 */
class DoctrineAnnotationReader implements ObjectReaderInterface
{
    /**
     * @var Reader
     */
    protected $annotationReader;

    /**
     * DoctrineAnnotationReader constructor.
     * @param Reader $annotationReader
     */
    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * Get a resource name.
     * This resource reader will first try to read any @Type annotation if present,
     * or use the reflection short name otherwise.
     *
     * @TODO: We sould have more sources for this: configuration
     * @TODO move from doctrine/common to doctrine/annotation
     *
     * @param mixed $resource
     *
     * @return string|null
     *
     * @throws \ReflectionException
     */
    public function getResourceName($resource): ?string
    {
        // If the resource is an empty collection, return null
        if ($this->isCollection($resource) && 0 === count($resource)) {
            return null;
        }

        $reflection = $this->isCollection($resource)
            ? new ReflectionClass(ClassUtils::getClass($resource[0]))
            : new ReflectionClass(ClassUtils::getClass($resource));

        /**
         * @var Type|null $annotation
         */
        $annotation = $this->annotationReader->getClassAnnotation($reflection, Type::class);

        if (null !== $annotation) {
            return $annotation->getType();
        }

        return strtolower($reflection->getShortName());
    }

    /**
     * Does an entity's property have the @embeddable annotation ?
     *
     * @param \ReflectionProperty $property
     *
     * @return bool
     */
    public function isPropertyEmbeddable(\ReflectionProperty $property)
    {
        return null !== $this->annotationReader->getPropertyAnnotation($property, Embeddable::class);
    }

    /**
     * Does an entity's property have the @exposable annotation ?
     *
     * @param \ReflectionProperty $property
     *
     * @return bool
     */
    public function isPropertyExposable(\ReflectionProperty $property)
    {
        return null !== $this->annotationReader->getPropertyAnnotation($property, Exposable::class);
    }

    /**
     * @inheritdoc
     * Uses reflection to guess all the exposed properties from a given resource.
     * The filter parameter is here to do the "sparse fieldset" feature.
     */
    public function getExposedProperties($resource, array $filter)
    {
        // @TODO we want to have different possible strategies for exposing properties
        // to include everything or nothing by default (or maybe scalars only ?)
        // as well has having multiple filtering strategies (fields=name,date...) such as all or nothing
        // and deep sparse fieldset (fields=label.name,gigs.date)

        $propertyList = [];
        // @TODO move from doctrine/common to doctrine/reflection
        $reflectionClass = new ReflectionClass(ClassUtils::getClass($resource));
        foreach ($reflectionClass->getProperties() as $property) {
            $propertyName = $property->getName();

            // current implementation takes everything if fields is unspecified or id + fields if specified
            if ($this->isPropertyExposable($property) && $this->isPropertyAvailable($propertyName, $filter)) {
                $propertyList[$propertyName] = $this->processValue($resource->{$this->getPropertyGetter($property)}());
            }
        }

        return $propertyList;
    }

    public function getPropertyValue($resource, string $name)
    {
        $reflectionClass = new ReflectionClass($resource);

        if ($resource instanceof Proxy) {
            $getterMethod = 'get'.ucfirst($name);
            $getter = $reflectionClass->getMethod($getterMethod);

            return $resource->{$getter->name}();
        }

        $property = $reflectionClass->getProperty(lcfirst($name));
        $getter = $this->getPropertyGetter($property);

        if ($getter) {
            return $resource->{$getter}();
        }

        return null;
    }

    /**
     * If the @Exposable annotation has a getter property, use that, otherwise use get_*Property*
     *
     * @param \ReflectionProperty $property
     *
     * @return string
     */
    private function getPropertyGetter(ReflectionProperty $property)
    {
        /**
         * @var Exposable|null
         */
        $exposable = $this->annotationReader->getPropertyAnnotation($property, Exposable::class);

        if (null !== $exposable && null !== $exposable->getGetter()) {
            return $exposable->getGetter();
        }

        return 'get'.ucfirst($property->getName());
    }

    /**
     * @TODO: should be configurable via annotation
     * @TODO: should be able to process more than dates
     * @param mixed $value
     *
     * @return string
     */
    private function processValue($value)
    {
        if ($value instanceof \DateTime) {
            return $value->format(\DateTime::ATOM);
        }

        return $value;
    }

    /**
     * @param mixed $resource
     *
     * @return bool
     */
    private function isCollection($resource)
    {
        if (is_array($resource) || $resource instanceof \Traversable || $resource instanceof \Countable) {
            return true;
        }

        return false;
    }

    /**
     * @param string $propertyName
     * @param array $filter
     *
     * @return bool
     */
    private function isPropertyAvailable(string $propertyName, array $filter)
    {
        return count($filter) > 0 ? in_array($propertyName, $filter) || $propertyName === 'id' : true;
    }
}
