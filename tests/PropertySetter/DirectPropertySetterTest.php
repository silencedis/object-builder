<?php

namespace SilenceDis\ObjectBuilder\Test\PropertySetter;

use PHPUnit\Framework\TestCase;
use SilenceDis\ObjectBuilder\PropertySetter\DirectPropertySetter;
use SilenceDis\ObjectBuilder\Test\Fixture\PublicPropertiesObject;

class DirectPropertySetterTest extends TestCase
{
    /**
     * If the value is valid and the property is public, it must be set correctly.
     *
     * @covers \SilenceDis\ObjectBuilder\PropertySetter\DirectPropertySetter::set
     */
    public function testSet_1()
    {
        $object = new PublicPropertiesObject();
        $property = 'foo';
        $value = 'test string';

        $setter = new DirectPropertySetter($object, $property, $value);
        $setter->set();

        $this->assertTrue($object->foo === $value);
    }
}
