<?php

namespace WizardsTest\Exception;

use WizardsRest\Exception\HttpException;
use PHPUnit\Framework\TestCase;

class HttpExceptionTest extends TestCase
{
    public function testException()
    {
        $exception = new HttpException(404, 'Not Found');
        $this->assertEquals($exception->getStatusCode(), 404);
        $this->assertEquals($exception->getMessage(), 'Not Found');
    }
}
