<?php

namespace WizardsRest\ObjectReader;

use Doctrine\Common\Util\ClassUtils;
use WizardsRest\Annotation\Embeddable;
use WizardsRest\Annotation\Exposable;
use Doctrine\Common\Annotations\Reader;
use WizardsRest\Annotation\Type;

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
     *
     * @TODO: We sould have more sources for this: configuration
     * @TODO move from doctrine/common to doctrine/annotation
     *
     * @param $resource
     *
     * @return string
     *
     * @throws \ReflectionException
     */
    public function getResourceName($resource)
    {
        $reflection = $resource instanceof \Traversable
            ? new \ReflectionClass(ClassUtils::getClass($resource[0]))
            : new \ReflectionClass(ClassUtils::getClass($resource));

        /**
         * @var Type|null $resourceNameAnnotation
         */
        $resourceNameAnnotation = $this->annotationReader->getClassAnnotation($reflection, Type::class);

        if (null !== $resourceNameAnnotation) {
            return $resourceNameAnnotation->getType();
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

    public function getExposedProperties($resource, array $filter)
    {
        // @TODO we want to have different possible strategies faut exposing properties
        // to include everything or nothing by default (or maybe scalars only ?)
        // as well has having multiple filtering strategies (fields=name,date...) such as all or nothing
        // and deep sparse fieldset (fields=label.name,gigs.date)

        $propertyList = [];
        // @TODO move from doctrine/common to doctrine/reflection
        $reflectionClass = new \ReflectionClass(ClassUtils::getClass($resource));
        foreach ($reflectionClass->getProperties() as $property) {
            $propertyName = $property->getName();

            // current implementation takes everything if fields is unspecified or id + fields if specified
            if (
                $this->isPropertyExposable($property) &&
                (count($filter) > 0 ? in_array($propertyName, $filter) || $propertyName === 'id' : true)
            ) {
                $propertyList[$propertyName] = $this->processValue($resource->{$this->getPropertyGetter($property)}());
            }
        }

        return $propertyList;
    }

    public function getPropertyValue($resource, string $name)
    {
        $reflectionClass = new \ReflectionClass($resource);
        $property = $reflectionClass->getProperty(strtolower($name));
        $getter = $this->getPropertyGetter($property);

        if ($getter) {
            return $resource->{$getter}();
        }

        return null;
    }

    /**
     * if annotation has a getter property, use that, otherwise use get_*Property*
     * @TODO document that
     * @param \ReflectionProperty $property
     * @return string
     */
    private function getPropertyGetter(\ReflectionProperty $property)
    {
        $exposable = $this->annotationReader->getPropertyAnnotation($property, Exposable::class);

        if (null !== $exposable && null !== $exposable->getGetter()) {
            return $exposable->getGetter();
        }

        return 'get'.ucfirst($property->getName());
    }

    /**
     * @TODO: should be configurable via annotation. should be able to process more than dates
     * @param $value
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
}
