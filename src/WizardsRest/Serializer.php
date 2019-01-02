<?php

namespace WizardsRest;

use League\Fractal\Manager;
use League\Fractal\Serializer\JsonApiSerializer;
use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\Resource\ResourceInterface;

/**
 * A service to help you serialize your fractal resource in the given format & specification.
 *
 * @package WizardsRest
 *
 * @author Romain Richard
 */
class Serializer
{
    const SPEC_JSONAPI = 'SPEC_JSONAPI';
    const SPEC_ARRAY = 'SPEC_ARRAY';
    const SPEC_DATA_ARRAY = 'SPEC_DATA_ARRAY';

    const FORMAT_JSON = 'FORMAT_JSON';
    const FORMAT_ARRAY = 'FORMAT_ARRAY';

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * Serializer constructor.
     *
     * @param Manager $manager
     * @param string $baseUrl
     */
    public function __construct(Manager $manager, $baseUrl = '')
    {
        $this->manager = $manager;
        $this->baseUrl = $baseUrl;
    }

    /**
     * Serialize a Fractal Resource to the given format & specification.
     *
     * @param ResourceInterface $resource
     * @param string $specification
     * @param string $format
     *
     * @return array|string
     */
    public function serialize(
        ResourceInterface $resource,
        string $specification = self::SPEC_DATA_ARRAY,
        string $format = self::FORMAT_ARRAY
    ) {
        $this->selectSerializer($specification);

        return $this->format($resource, $format);
    }

    private function selectSerializer($specification)
    {
        switch ($specification) {
            case self::SPEC_JSONAPI:
                $this->manager->setSerializer(new JsonApiSerializer($this->baseUrl));
                break;
            case self::SPEC_ARRAY:
                $this->manager->setSerializer(new ArraySerializer());
                break;
        }
    }

    private function format($resource, $format)
    {
        switch ($format) {
            case self::FORMAT_JSON:
                return $this->manager->createData($resource)->toJson();
        }

        return $this->manager->createData($resource)->toArray();
    }
}
