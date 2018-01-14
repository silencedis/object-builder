<?php

namespace SilenceDis\ObjectBuilder\Test\Builder;

use PHPUnit\Framework\TestCase;
use SilenceDis\ObjectBuilder\Builder\DateTimeBuilder;

class DateTimeBuilderTest extends TestCase
{
    /**
     * @covers \SilenceDis\ObjectBuilder\Builder\DateTimeBuilder::build
     */
    public function testBuild()
    {
        $rawData = '2018-01-01 02:30:40';
        $builder = new DateTimeBuilder();
        $actualResult = $builder->build($rawData);
        $expectedResult = new \DateTime($rawData);
        $this->assertEquals($expectedResult, $actualResult);
    }
}
