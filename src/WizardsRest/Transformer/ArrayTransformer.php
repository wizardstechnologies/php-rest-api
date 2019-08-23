<?php

namespace WizardsRest\Transformer;

use WizardsRest\Exception\IncludeNotFoundException;
use WizardsRest\ObjectReader\ObjectReaderInterface;
use League\Fractal\TransformerAbstract;

/**
 * A generic Fractal Transformer to transform an entity/document to a Fractal Resource.
 *
 * @author Romain Richard
 */
class ArrayTransformer extends TransformerAbstract
{
    /**
     * List of fields possible to display
     *
     * @var array
     */
    private $availableFields = [];

    /**
     * @param $array
     * @return array
     */
    public function transform($array)
    {
        return $array;
    }

    public function setAvailableFields(array $fields)
    {
        $this->availableFields = $fields;
    }
}
