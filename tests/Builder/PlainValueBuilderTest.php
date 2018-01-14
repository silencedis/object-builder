<?php

namespace SilenceDis\ObjectBuilder\Test\Builder;

use PHPUnit\Framework\TestCase;
use SilenceDis\ObjectBuilder\Builder\PlainValueBuilder;

class PlainValueBuilderTest extends TestCase
{
    /**
     * The PlainValueBuilder returns the value as is.
     *
     * @covers       \SilenceDis\ObjectBuilder\Builder\PlainValueBuilder::build
     *
     * @dataProvider dataBuild
     *
     * @param $rawValue
     */
    public function testBuild($rawValue)
    {
        $builder = new PlainValueBuilder();
        $result = $builder->build($rawValue);
        $this->assertTrue($result === $rawValue);
    }

    /**
     * @return array
     */
    public function dataBuild()
    {
        return [
            ['string'],
            [1],
            [1.1],
            [null],
            [true],
            [false],
            [new \stdClass()],
            [
                function () {
                },
            ],
        ];
    }
}
