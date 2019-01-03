<?php

namespace WizardsTest\Paginator;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Router;
use WizardsRest\Paginator\ArrayPagerfantaPaginator;
use WizardsRest\Parser\RestQueryParser;
use Zend\Diactoros\ServerRequest;

class ArrayPagerfantaPaginatorTest extends TestCase
{
    public function testPaginate()
    {
        $routerMock = $this->createMock(Router::class);
        $paginator = new ArrayPagerfantaPaginator($routerMock);
        $collection = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25];
        $request = new ServerRequest(
            [],
            [],
            '/posts',
            'GET'
        );
        $this->assertEquals(RestQueryParser::DEFAULT_LIMIT, count($paginator->paginate($collection, $request)));

        $request2 = new ServerRequest(
            [],
            [],
            '/posts',
            'GET',
            'php://input',
            [],
            [],
            [RestQueryParser::PARAMETER_LIMIT => '5']
        );
        $this->assertEquals(5, count($paginator->paginate($collection, $request2)));
    }

    public function testPaginateCollection()
    {
        $routerMock = $this->createMock(Router::class);
        $paginator = new ArrayPagerfantaPaginator($routerMock);
        $collection = new ArrayCollection([1, 2, 3, 4, 5]);
        $request = new ServerRequest(
            [],
            [],
            '/posts',
            'GET'
        );
        $this->assertEquals(5, count($paginator->paginate($collection, $request)));
    }
}
