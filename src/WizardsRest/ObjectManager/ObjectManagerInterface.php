<?php

namespace App\ObjectManager;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface ObjectManagerInterface.
 *
 * @author Romain Richard
 */
interface ObjectManagerInterface
{
    /**
     * @param $className
     * @param $request
     *
     * @return mixed
     */
    public function getPaginatedCollection($className, $request);

    /**
     * @param $request
     * @return mixed
     */
    public function getPaginationAdapter($request);
}
