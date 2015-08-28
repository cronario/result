<?php

namespace Ik\Lib\Test\Exception;

use Result\MapBuilder;

Class ResultMapBuilderTest extends \PHPUnit_Framework_TestCase
{

    private static $filename;

    public function setUp()
    {
        self::$filename = sys_get_temp_dir() . '/' . uniqid() . '.php';
        touch(self::$filename);
    }

    public function tearDown()
    {
        unlink(self::$filename);
    }

    public function testBuilder()
    {
        MapBuilder::build(self::$filename, __DIR__);
        $data = include self::$filename;
        $this->assertEquals($data, MapBuilder::getResults());
        $this->assertCount(1, $data);
    }
}
