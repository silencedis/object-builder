<?php

namespace SilenceDis\ObjectBuilder\Test\PropertySetter;

use PHPUnit\Framework\TestCase;
use SilenceDis\ObjectBuilder\PropertySetter\SetPropertyThroughSetter;
use SilenceDis\ObjectBuilder\PropertySetter\PropertySetterExceptionInterface;
use SilenceDis\ObjectBuilder\Test\Fixture\PrivatePropertiesObject;
use SilenceDis\ObjectBuilder\Test\Fixture\PrivatePropertyAndSetterObject;
use SilenceDis\PHPUnitMockHelper\MockHelper;

class SetPropertyThroughSetterTest extends TestCase
{
    /**
     * If the `object` constructor argument is not object, the TypeError exception will be thrown.
     *
     * @covers       \SilenceDis\ObjectBuilder\PropertySetter\SetPropertyThroughSetter::__construct
     * @dataProvider dataInvalidObjectValues
     * @param $invalidObjectValue
     */
    public function testConstructor_1($invalidObjectValue)
    {
        $this->expectException(\TypeError::class);
        // The "property" and "value" parameters of constructor don't matter.
        new SetPropertyThroughSetter($invalidObjectValue, 'foo', 'bar');
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
     * @covers \SilenceDis\ObjectBuilder\PropertySetter\SetPropertyThroughSetter::__construct
     */
    public function testConstructor_2()
    {
        $object = new PrivatePropertiesObject();
        $property = 'notExistedProperty'; // Actually, the setter of this property doesn't exist.
        $value = 'test string'; // The value doesn't matter in this test.
        $this->expectException(PropertySetterExceptionInterface::class);
        new SetPropertyThroughSetter($object, $property, $value);
    }

    /**
     * If the property is not public, the PropertySetterException must be thrown.
     *
     * @covers \SilenceDis\ObjectBuilder\PropertySetter\SetPropertyThroughSetter::__construct
     */
    public function testConstructor_3()
    {
        $object = new PrivatePropertyAndSetterObject();
        $property = 'foo';
        $value = 'test string'; // Actually, the setter of this property is not accessible.
        $this->expectException(PropertySetterExceptionInterface::class);
        new SetPropertyThroughSetter($object, $property, $value);
    }

    /**
     * If the value is valid and the property is public, it must be set correctly.
     *
     * @covers \SilenceDis\ObjectBuilder\PropertySetter\SetPropertyThroughSetter::set
     * @throws \SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException
     */
    public function testSet_1()
    {
        $property = 'property1';
        $setterMethod = 'setProperty1';
        $value = 'test string';
        /** @var \PHPUnit_Framework_MockObject_MockObject|SetPropertyThroughSetter $setter */
        $object = (new MockHelper($this))->mockObject(
            PrivatePropertiesObject::class,
            [
                'methods' => [$setterMethod],
            ]
        );

        $setter = new SetPropertyThroughSetter($object, $property, $value);
        $object->expects($this->once())->method($setterMethod)->with($value);
        $setter->set();
    }
}
