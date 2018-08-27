<?php

namespace WizardsRest\Annotation;

/**
 * Tell the serializer if a property is exposable in a representation.
 *
 * @Annotation
 *
 * @Target("PROPERTY")
 */
class Exposable
{
    /**
     * @var string $getter
     */
    private $getter;

    /**
     * Constructor.
     *
     * @param array $data An array of key/value parameters
     *
     * @throws \BadMethodCallException
     */
    public function __construct(array $data)
    {
        $this->setGetter(isset($data['value']) ? $data['value'] : null);
    }

    /**
     * @return string
     */
    public function getGetter()
    {
        return $this->getter;
    }

    /**
     * @param string $getter
     */
    public function setGetter(string $getter)
    {
        $this->getter = $getter;
    }
}
