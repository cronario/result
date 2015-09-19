<?php


namespace Result\Test;


class BaseExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testPrevious()
    {
        $prev = new \RuntimeException('prev');
        $exception = new \Result\BaseException(10, $prev);
        $this->assertInstanceOf('\\RuntimeException', $exception->getPrevious());
    }

    public function testMessageArg()
    {
        $exception = new \Result\BaseException('Foo is %s', 'bar');
        $this->assertEquals('Foo is bar', $exception->getMessage());
    }

    public function testExceptionToArray()
    {
        $prev = new \RuntimeException('prev');
        $prevBase = new \Result\BaseException('Foo', $prev);
        $exception = new \Result\BaseException(10, $prevBase);
        $this->assertArrayHasKey('previous', $exception->toArray());
    }

    public function testExceptionToJson()
    {
        $exception = new \Result\BaseException('Msg');
        $this->assertEquals(json_encode($exception->toArray()), $exception->toJson());
    }
}
