<?php

namespace WizardsRest\ObjectManager;

use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * object manager for laravel
 *
 * @todo try it !
 *
 * @author Romain Richard
 */
class IlluminateObjectManager implements ObjectManagerInterface
{
    /**
     * @var LengthAwarePaginator
     */
    private $paginator;

    /**
     * @param $className
     * @param $request
     *
     * @return array|mixed|\Traversable
     */
    public function getPaginatedCollection($className, $request)
    {
        $this->paginator = $className::paginate();

        return $this->paginator->getCollection();
    }

    /**
     * @param $request
     * @return IlluminatePaginatorAdapter|null
     */
    public function getPaginationAdapter($request)
    {
        if ($this->paginator) {
            return new IlluminatePaginatorAdapter($this->paginator);
        }

        return null;
    }
}
