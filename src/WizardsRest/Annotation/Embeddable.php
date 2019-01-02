<?php

namespace WizardsRest\Annotation;

/**
 * Tell the serializer if a property embeddable in a representation.
 *
 * @Annotation
 *
 * @Target({"PROPERTY", "CLASS"})
 */
class Embeddable
{
    /**
     * @var string
     */
    private $routeName;

    /**
     * @var string
     */
    private $collectionRouteName;

    /**
     * Constructor.
     *
     * @param array $data An array of key/value parameters
     *
     * @throws \BadMethodCallException
     */
    public function __construct(array $data)
    {
        if (isset($data['value'])) {
            $data['routeName'] = $data['value'];
            unset($data['value']);
        }

        foreach ($data as $key => $value) {
            $method = 'set'.str_replace('_', '', (string) $key);
            if (!method_exists($this, $method)) {
                throw new \BadMethodCallException(
                    sprintf('Unknown property "%s" on annotation "%s".', $key, get_class($this))
                );
            }
            $this->$method($value);
        }
    }

    /**
     * @return string
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * @param string $routeName
     */
    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;
    }

    /**
     * @return string
     */
    public function getCollectionRouteName()
    {
        return $this->collectionRouteName;
    }

    /**
     * @param string $collectionRouteName
     */
    public function setCollectionRouteName($collectionRouteName)
    {
        $this->collectionRouteName = $collectionRouteName;
    }
}
