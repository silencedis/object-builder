<?php

namespace SilenceDis\ObjectBuilder\Test\PropertySetter;

use PHPUnit\Framework\TestCase;
use SilenceDis\ObjectBuilder\PropertySetter\PropertySetterExceptionInterface;
use SilenceDis\ObjectBuilder\PropertySetter\SetPropertyDirectly;
use SilenceDis\ObjectBuilder\Test\Fixture\NoPropertiesObject;
use SilenceDis\ObjectBuilder\Test\Fixture\PrivatePropertiesObject;
use SilenceDis\ObjectBuilder\Test\Fixture\PublicPropertiesObject;

class SetPropertyDirectlyTest extends TestCase
{
    # region set

    /**
     * If the `object` constructor argument is not object, the TypeError exception will be thrown.
     *
     * @covers       \SilenceDis\ObjectBuilder\PropertySetter\SetPropertyDirectly::set
     * @dataProvider dataInvalidObjectValues
     * @param $invalidObjectValue
     * @throws \SilenceDis\ObjectBuilder\PropertySetter\PropertySetterException
     * @throws \TypeError
     */
    public function testSet_1($invalidObjectValue)
    {
        $this->expectException(\TypeError::class);
        // The "property" and "value" parameters of constructor don't matter.
        $setter = new SetPropertyDirectly();
        $setter->set($invalidObjectValue, 'foo', 'bar');
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
     * @covers \SilenceDis\ObjectBuilder\PropertySetter\SetPropertyDirectly::set
     *
     * @throws \SilenceDis\ObjectBuilder\PropertySetter\PropertySetterException
     * @throws \TypeError
     */
    public function testSet_2()
    {
        $object = new PublicPropertiesObject();
        $property = 'notExistedProperty';
        $value = 'test string'; // The value doesn't matter in this test.
        $this->expectException(PropertySetterExceptionInterface::class);
        $setter = new SetPropertyDirectly();
        $setter->set($object, $property, $value);
    }

    /**
     * If the property is not public, the PropertySetterException must be thrown.
     *
     * @covers \SilenceDis\ObjectBuilder\PropertySetter\SetPropertyDirectly::set
     *
     * @throws \TypeError
     * @throws \SilenceDis\ObjectBuilder\PropertySetter\PropertySetterException
     */
    public function testSet_3()
    {
        $object = new PrivatePropertiesObject();
        $property = 'foo';
        $value = 'test string'; // The value doesn't matter in this test.
        $this->expectException(PropertySetterExceptionInterface::class);
        $setter = new SetPropertyDirectly();
        $setter->set($object, $property, $value);
    }

    /**
     * If the value is valid and the property is public, it must be set correctly.
     *
     * @covers \SilenceDis\ObjectBuilder\PropertySetter\SetPropertyDirectly::set
     *
     * @throws \TypeError
     * @throws \SilenceDis\ObjectBuilder\PropertySetter\PropertySetterException
     */
    public function testSet_4()
    {
        $object = new PublicPropertiesObject();
        $property = 'foo';
        $value = 'test string';

        $setter = new SetPropertyDirectly();
        $setter->set($object, $property, $value);

        $this->assertTrue($object->foo === $value);
    }

    # endregion

    # region canSet

    /**
     * The method "canSet" returns a boolean.
     * @covers       \SilenceDis\ObjectBuilder\PropertySetter\SetPropertyDirectly::canSet
     * @dataProvider dataCanSet
     * @param $object
     * @param $propertyName
     * @param $expectedResult
     */
    public function testCanSet_1($object, $propertyName, $expectedResult)
    {
        $objectReflection = new \ReflectionClass($object);
        $setter = new SetPropertyDirectly();
        $actualResult = $setter->canSet($objectReflection, $propertyName, null);
        $this->assertTrue(is_bool($actualResult));
    }

    /**
     * @covers       \SilenceDis\ObjectBuilder\PropertySetter\SetPropertyDirectly::canSet
     * @dataProvider dataCanSet
     *
     * @param $object
     * @param $propertyName
     * @param $expectedResult
     */
    public function testCanSet_2($object, $propertyName, $expectedResult)
    {
        $objectReflection = new \ReflectionClass($object);
        $setter = new SetPropertyDirectly();
        $actualResult = $setter->canSet($objectReflection, $propertyName, null);
        $this->assertEquals($expectedResult, $actualResult);
    }


    public function dataCanSet()
    {
        return [
            // The object doesn't have any properties
            [new NoPropertiesObject(), 'foo', false],
            // The object doesn't have an appropriate public property (actually, it's private).
            [new PrivatePropertiesObject(), 'asdf', false],
            // The object has an appropriate public property
            [new PublicPropertiesObject(), 'foo', true],
        ];
    }

    # endregion
}
