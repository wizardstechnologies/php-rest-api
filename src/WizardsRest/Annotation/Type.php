<?php

namespace WizardsRest\Annotation;

/**
 * Define an type for serialization (useful for jsonapi)
 *
 * @Annotation
 *
 * @Target({"CLASS", "METHOD"})
 */
class Type
{
    /**
     * @var string|null
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type = null)
    {
        $this->type = $type;
    }
}
