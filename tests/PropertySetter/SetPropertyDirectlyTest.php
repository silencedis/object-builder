<?php

namespace SilenceDis\ObjectBuilder\Test\PropertySetter;

use PHPUnit\Framework\TestCase;
use SilenceDis\ObjectBuilder\PropertySetter\PropertySetterExceptionInterface;
use SilenceDis\ObjectBuilder\PropertySetter\SetPropertyDirectly;
use SilenceDis\ObjectBuilder\Test\Fixture\PrivatePropertiesObject;
use SilenceDis\ObjectBuilder\Test\Fixture\PublicPropertiesObject;

class SetPropertyDirectlyTest extends TestCase
{
    # region constructor

    /**
     * If the `object` constructor argument is not object, the TypeError exception will be thrown.
     *
     * @covers       \SilenceDis\ObjectBuilder\PropertySetter\SetPropertyDirectly::__construct
     * @dataProvider dataInvalidObjectValues
     * @param $invalidObjectValue
     */
    public function testConstructor_1($invalidObjectValue)
    {
        $this->expectException(\TypeError::class);
        // The "property" and "value" parameters of constructor don't matter.
        $setter = new SetPropertyDirectly($invalidObjectValue, 'foo', 'bar');
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
     * @covers \SilenceDis\ObjectBuilder\PropertySetter\SetPropertyDirectly::__construct
     */
    public function testConstructor_2()
    {
        $object = new PublicPropertiesObject();
        $property = 'notExistedProperty';
        $value = 'test string'; // The value doesn't matter in this test.
        $this->expectException(PropertySetterExceptionInterface::class);
        new SetPropertyDirectly($object, $property, $value);
    }

    /**
     * If the property is not public, the PropertySetterException must be thrown.
     *
     * @covers \SilenceDis\ObjectBuilder\PropertySetter\SetPropertyDirectly::__construct
     */
    public function testConstructor_3()
    {
        $object = new PrivatePropertiesObject();
        $property = 'property1';
        $value = 'test string'; // The value doesn't matter in this test.
        $this->expectException(PropertySetterExceptionInterface::class);
        new SetPropertyDirectly($object, $property, $value);
    }

    # endregion

    # region set

    /**
     * If the value is valid and the property is public, it must be set correctly.
     *
     * @covers \SilenceDis\ObjectBuilder\PropertySetter\SetPropertyDirectly::set
     */
    public function testSet_1()
    {
        $object = new PublicPropertiesObject();
        $property = 'foo';
        $value = 'test string';

        $setter = new SetPropertyDirectly($object, $property, $value);
        $setter->set();

        $this->assertTrue($object->foo === $value);
    }

    # endregion

    # region canSet

    /**
     * The method "canSet" returns a boolean.
     */
//    public function testCanSet_1()
//    {
//        $object = new PublicPropertiesObject();
//        $property = 'foo';
//        $value = 'test string';
//        $setter = new SetPropertyDirectly($object, $property, $value);
//        $actualResult = $setter->canSet();
//        $this->assertTrue(is_bool($actualResult));
//    }

    # endregion
}
