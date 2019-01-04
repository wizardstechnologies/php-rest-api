<?php

namespace WizardsTest\ObjectManager;

use PHPUnit\Framework\TestCase;
use WizardsRest\ObjectManager\ArrayObjectManager;
use WizardsRest\Parser\RestQueryParser;
use Zend\Diactoros\ServerRequest;

class ArrayObjectManagerTest extends TestCase
{
    public function testFetchCollection()
    {
        $objectManager = new ArrayObjectManager();
        $france = [
            'id' => 1,
            'name' => 'France',
            'domain' => 'a.com'
        ];
        $brasil = [
            'id' => 2,
            'name' => 'Brasil',
            'domain' => 'b.com'
        ];
        $collection = [$france, $brasil];

        // filter by id >
        $request = new ServerRequest(
            [],
            [],
            '/posts',
            'GET',
            'php://input',
            [],
            [],
            [
                RestQueryParser::PARAMETER_FILTER => ['id' => '1'],
                RestQueryParser::PARAMETER_FILTER_OPERATOR => ['id' => '>']
            ]
        );
        $this->assertEquals([$brasil], $objectManager->fetchCollection($collection, $request));

        // sort by -domain
        $request = new ServerRequest(
            [],
            [],
            '/posts',
            'GET',
            'php://input',
            [],
            [],
            [
                RestQueryParser::PARAMETER_SORT => '-domain',
            ]
        );
        $this->assertEquals([$brasil, $france], $objectManager->fetchCollection($collection, $request));
    }
}
