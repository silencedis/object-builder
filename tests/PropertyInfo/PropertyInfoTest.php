<?php

namespace SilenceDis\ObjectBuilder\Test\PropertyInfo;

use PHPUnit\Framework\TestCase;
use SilenceDis\ObjectBuilder\PropertyInfo\PropertyInfo;
use SilenceDis\ObjectBuilder\Test\Fixture\NoPropertiesObject;
use SilenceDis\ObjectBuilder\Test\Fixture\PrivatePropertiesObject;
use SilenceDis\ObjectBuilder\Test\Fixture\PublicPropertiesObject;

class PropertyInfoTest extends TestCase
{
    /**
     * @covers       \SilenceDis\ObjectBuilder\PropertyInfo\PropertyInfo::hasAccessibleField
     * @dataProvider dataHasAccessibleField
     *
     * @param object $object
     * @param string $propertyName
     * @param bool $expectedResult
     */
    public function testHasAccessibleField($object, $propertyName, $expectedResult)
    {
        $info = new PropertyInfo($object, $propertyName);
        $actualResult = $info->hasAccessibleField();
        $this->assertTrue($expectedResult === $actualResult);
    }

    public function dataHasAccessibleField()
    {
        return [
            [
                new PublicPropertiesObject(),
                'foo',
                true,
            ],
            [
                new PrivatePropertiesObject(),
                'foo',
                false,
            ],
            [
                new NoPropertiesObject(),
                'foo', // This property doesn't exist in the object
                false,
            ],
        ];
    }
}
