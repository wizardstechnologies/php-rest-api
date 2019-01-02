<?php

namespace WizardsTest\Parser;

use PHPUnit\Framework\TestCase;
use WizardsRest\Parser\RestQueryParser;
use Zend\Diactoros\ServerRequest;

class RestQueryParserTest extends TestCase
{
    public function testGet()
    {
        $request = new ServerRequest(
            [],
            [],
            '/posts',
            'GET',
            'php://input',
            [],
            [],
            [
                RestQueryParser::PARAMETER_LIMIT => '5',
                RestQueryParser::PARAMETER_PAGE => '2',
                RestQueryParser::PARAMETER_SORT => 'name',
                RestQueryParser::PARAMETER_FILTER => ['test' => '42'],
                RestQueryParser::PARAMETER_FILTER_OPERATOR => ['test' => '>'],
                'unused' => 'test',

            ]
        );
        $parser = new RestQueryParser($request);

        $this->assertEquals('5', $parser->get(RestQueryParser::PARAMETER_LIMIT));
        $this->assertEquals('2', $parser->get(RestQueryParser::PARAMETER_PAGE));
        $this->assertEquals('name', $parser->get(RestQueryParser::PARAMETER_SORT));
        $this->assertEquals(['test' => '42'], $parser->get(RestQueryParser::PARAMETER_FILTER));
        $this->assertEquals(['test' => '>'], $parser->get(RestQueryParser::PARAMETER_FILTER_OPERATOR));
        $this->assertEquals(null, $parser->get('unused'));
    }

    public function testGetDefaults()
    {
        $request = new ServerRequest(
            [],
            [],
            '/posts',
            'GET'
        );
        $parser = new RestQueryParser($request);

        $this->assertEquals(RestQueryParser::DEFAULT_LIMIT, $parser->get(RestQueryParser::PARAMETER_LIMIT));
        $this->assertEquals(RestQueryParser::DEFAULT_PAGE, $parser->get(RestQueryParser::PARAMETER_PAGE));
        $this->assertEquals(RestQueryParser::DEFAULT_SORT, $parser->get(RestQueryParser::PARAMETER_SORT));
        $this->assertEquals(RestQueryParser::DEFAULT_FILTER, $parser->get(RestQueryParser::PARAMETER_FILTER));
        $this->assertEquals(RestQueryParser::DEFAULT_FILTER_OPERATOR, $parser->get(RestQueryParser::PARAMETER_FILTER_OPERATOR));
        $this->assertEquals(null, $parser->get('unused'));
    }

    public function testParseSorting()
    {
        $request = new ServerRequest(
            [],
            [],
            '/posts',
            'GET',
            'php://input',
            [],
            [],
            [RestQueryParser::PARAMETER_SORT => 'name,-test,value']
        );
        $parser = new RestQueryParser($request);

        $this->assertEquals(
            ['name' => 'asc', 'test' => 'desc', 'value' => 'asc'],
            $parser->getParsedSorting()
        );
    }
}
