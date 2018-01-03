<?php

namespace SilenceDis\ObjectBuilder\Test\PropertySetter;

use PHPUnit\Framework\TestCase;
use SilenceDis\ObjectBuilder\PropertySetter\DirectPropertySetter;
use SilenceDis\ObjectBuilder\PropertySetter\PropertySetterExceptionInterface;
use SilenceDis\ObjectBuilder\Test\Fixture\PrivatePropertiesObject;
use SilenceDis\ObjectBuilder\Test\Fixture\PublicPropertiesObject;

class DirectPropertySetterTest extends TestCase
{
    /**
     * If the `object` constructor argument is not object, the TypeError exception will be thrown.
     *
     * @covers       \SilenceDis\ObjectBuilder\PropertySetter\DirectPropertySetter::__construct
     * @dataProvider dataInvalidObjectValues
     * @param $invalidObjectValue
     */
    public function testConstructor_1($invalidObjectValue)
    {
        $this->expectException(\TypeError::class);
        // The "property" and "value" parameters of constructor don't matter.
        new DirectPropertySetter($invalidObjectValue, 'foo', 'bar');
    }

    public function dataInvalidObjectValues()
    {
        return [
            ['string'],
            [true],
            [false],
            [1],
            [1.1],
            [[]],
        ];
    }

    /**
     * If the property doesn't exist in the object, the PropertySetterException must be thrown.
     *
     * @covers \SilenceDis\ObjectBuilder\PropertySetter\DirectPropertySetter::__construct
     */
    public function testConstructor_2()
    {
        $object = new PublicPropertiesObject();
        $property = 'notExistedProperty';
        $value = 'test string'; // The value doesn't matter in this test.
        $this->expectException(PropertySetterExceptionInterface::class);
        new DirectPropertySetter($object, $property, $value);
    }

    /**
     * If the property is not public, the PropertySetterException must be thrown.
     *
     * @covers \SilenceDis\ObjectBuilder\PropertySetter\DirectPropertySetter::__construct
     */
    public function testConstructor_3()
    {
        $object = new PrivatePropertiesObject();
        $property = 'property1';
        $value = 'test string'; // The value doesn't matter in this test.
        $this->expectException(PropertySetterExceptionInterface::class);
        new DirectPropertySetter($object, $property, $value);
    }

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
