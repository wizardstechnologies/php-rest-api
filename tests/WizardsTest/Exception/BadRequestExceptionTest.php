<?php

namespace WizardsTest\Exception;

use WizardsRest\Exception\BadRequestException;
use PHPUnit\Framework\TestCase;

class BadRequestExceptionTest extends TestCase
{
    public function testException()
    {
        $exception = new BadRequestException();
        $this->assertEquals($exception->getStatusCode(), 400);
    }
}
