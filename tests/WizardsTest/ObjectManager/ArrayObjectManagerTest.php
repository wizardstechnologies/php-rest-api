<?php

namespace WizardsTest\ObjectManager;

use PHPUnit\Framework\TestCase;
use WizardsRest\ObjectManager\ArrayObjectManager;
use WizardsRest\Parser\RestQueryParser;
use Nyholm\Psr7\ServerRequest;

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
        $china = [
            'id' => 3,
            'name' => 'China',
            'domain' => 'c.com'
        ];
        $collection = [$china, $france, $brasil];

        // filter by id >
        $request = new ServerRequest('GET', '/posts');
        $request = $request->withQueryParams(
            [
                RestQueryParser::PARAMETER_FILTER => ['id' => '1'],
                RestQueryParser::PARAMETER_FILTER_OPERATOR => ['id' => '>'],
                RestQueryParser::PARAMETER_SORT => 'id',
            ]
        );
        $this->assertEquals([$brasil, $china], $objectManager->fetchCollection($collection, $request));

        // sort by -domain
        $request = $request->withQueryParams(
            [
                RestQueryParser::PARAMETER_SORT => '-domain',
            ]
        );
        $this->assertEquals([$china, $brasil, $france], $objectManager->fetchCollection($collection, $request));


        // sort by -domain
        $request = $request->withQueryParams(
            [
                RestQueryParser::PARAMETER_FILTER => ['name' => 'Brasil,France'],
                RestQueryParser::PARAMETER_FILTER_OPERATOR => ['name' => 'in']
            ]
        );
        $this->assertEquals([$france, $brasil], $objectManager->fetchCollection($collection, $request));
    }
}
