<?php

namespace WizardsRest\Annotation;

/**
 * Define an type for serialization (useful for jsonapi)
 *
 * @Annotation
 *
 * @Target("CLASS")
 */
class Type
{
    /**
     * @var string $type
     */
    private $type;

    /**
     * Constructor.
     *
     * @param array $data An array of key/value parameters
     *
     * @throws \BadMethodCallException
     */
    public function __construct(array $data)
    {
        $this->setType(isset($data['value']) ? $data['value'] : null);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     */
    public function setType(string $type = null)
    {
        $this->type = $type;
    }
}
