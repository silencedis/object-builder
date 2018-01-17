<?php

namespace SilenceDis\ObjectBuilder\Test\PropertySetter;

use PHPUnit\Framework\TestCase;
use SilenceDis\ObjectBuilder\Builder\BuilderInterface;
use SilenceDis\ObjectBuilder\BuildersContainer\BuildersContainerInterface;
use SilenceDis\ObjectBuilder\PropertySetter\PropertySetterExceptionInterface;
use SilenceDis\ObjectBuilder\PropertySetter\SetPropertyThroughSetter;
use SilenceDis\ObjectBuilder\Test\Fixture\ExampleInterface;
use SilenceDis\ObjectBuilder\Test\Fixture\NoPropertiesObject;
use SilenceDis\ObjectBuilder\Test\Fixture\PrivatePropertiesObject;
use SilenceDis\ObjectBuilder\Test\Fixture\PrivatePropertyAndSetterObject;
use SilenceDis\ObjectBuilder\Test\Fixture\SettersWithNotOneArgumentObject;
use SilenceDis\ObjectBuilder\Test\Fixture\TypeHintedButNotRequiredPropertyObject;
use SilenceDis\ObjectBuilder\Test\Fixture\TypeHintedPropertyObject;
use SilenceDis\PHPUnitMockHelper\MockHelper;
use SilenceDis\ProtectedMembersAccessor\ProtectedMembersAccessor;

/**
 * Class SetPropertyThroughSetterTest
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 *
 * @coversDefaultClass \SilenceDis\ObjectBuilder\PropertySetter\SetPropertyThroughSetter
 */
class SetPropertyThroughSetterTest extends TestCase
{
    /**
     * @param array $mockConfig
     * @return \PHPUnit_Framework_MockObject_MockObject|BuildersContainerInterface
     * @throws \SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException
     */
    private function getBuildersContainerMock($mockConfig = [])
    {
        /** @var BuildersContainerInterface $buildersContainer */
        return (new MockHelper($this))->mockObject(
            BuildersContainerInterface::class,
            [
                'mockType' => MockHelper::MOCK_TYPE_ABSTRACT,
            ],
            $mockConfig
        );
    }

    # region set

    /**
     * If the `object` constructor argument is not object, the TypeError exception will be thrown.
     *
     * @covers ::__construct
     * @dataProvider dataInvalidObjectValues
     *
     * @param $invalidObjectValue
     *
     * @throws \SilenceDis\ObjectBuilder\PropertySetter\PropertySetterException
     * @throws \TypeError
     * @throws \SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException
     */
    public function testSet_1($invalidObjectValue)
    {
        $this->expectException(\TypeError::class);
        // The "property" and "value" parameters of constructor don't matter.
        $setter = new SetPropertyThroughSetter($this->getBuildersContainerMock());
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
     * If the method doesn't exist in the object, the PropertySetterException must be thrown.
     *
     * @covers ::__construct
     *
     * @throws \TypeError
     * @throws \SilenceDis\ObjectBuilder\PropertySetter\PropertySetterException
     * @throws \SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException
     */
    public function testSet_2()
    {
        $object = new PrivatePropertiesObject();
        $property = 'notExistedProperty'; // Actually, the setter of this property doesn't exist.
        $value = 'test string'; // The value doesn't matter in this test.
        $this->expectException(PropertySetterExceptionInterface::class);
        $setter = new SetPropertyThroughSetter($this->getBuildersContainerMock());
        $setter->set($object, $property, $value);
    }

    /**
     * If the method is not public, the PropertySetterException must be thrown.
     *
     * @covers ::__construct
     *
     * @throws \TypeError
     * @throws \SilenceDis\ObjectBuilder\PropertySetter\PropertySetterException
     * @throws \SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException
     */
    public function testSet_3()
    {
        $object = new PrivatePropertyAndSetterObject();
        $property = 'foo';
        $value = 'test string'; // Actually, the setter of this property is not accessible.
        $this->expectException(PropertySetterExceptionInterface::class);
        $setter = new SetPropertyThroughSetter($this->getBuildersContainerMock());
        $setter->set($object, $property, $value);
    }

    /**
     * If the value is valid and the method is public, it must be set correctly.
     *
     * @covers ::set
     *
     * @throws \SilenceDis\ObjectBuilder\PropertySetter\PropertySetterException
     * @throws \SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException
     * @throws \TypeError
     */
    public function testSet_4()
    {
        $property = 'foo';
        $setterMethod = 'setFoo';
        $value = 'test string';
        /** @var \PHPUnit_Framework_MockObject_MockObject|SetPropertyThroughSetter $setter */
        $object = (new MockHelper($this))->mockObject(
            PrivatePropertiesObject::class,
            [
                'methods' => [$setterMethod],
            ]
        );

        $setter = new SetPropertyThroughSetter($this->getBuildersContainerMock());
        $object->expects($this->once())->method($setterMethod)->with($value);
        $setter->set($object, $property, $value);
    }

    /**
     * If the "object" variable isn't an object, the TypeError exception will be thrown.
     *
     * @covers ::set
     *
     * @dataProvider dataInvalidObjects
     *
     * @param $object
     *
     * @throws \SilenceDis\ObjectBuilder\PropertySetter\PropertySetterException
     * @throws \SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException
     * @throws \TypeError
     */
    public function testSet_5($object)
    {
        $propertyName = 'foo'; // The property name doesn't matter
        $value = null; // The value doesn't matter
        $setter = new SetPropertyThroughSetter($this->getBuildersContainerMock());

        $this->expectException(\TypeError::class);
        $setter->set($object, $propertyName, $value);
    }

    /**
     * @return array
     */
    public function dataInvalidObjects()
    {
        return [
            ['string'],
            [1],
            [1.1],
            [null],
            [true],
            [false],
            [[]],
        ];
    }

    /**
     * The {@see \SilenceDis\ObjectBuilder\PropertySetter\PropertySetterExceptionInterface} exception
     * will be thrown, if cannot set the property.
     *
     * @covers ::set
     *
     * @dataProvider dataNotSettable
     *
     * @param object $object
     * @param string $propertyName
     * @param mixed $value
     *
     * @throws \SilenceDis\ObjectBuilder\PropertySetter\PropertySetterException
     * @throws \SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException
     * @throws \TypeError
     */
    public function testSet_6($object, $propertyName, $value)
    {
        $setter = new SetPropertyThroughSetter($this->getBuildersContainerMock());
        $this->expectException(PropertySetterExceptionInterface::class);
        $setter->set($object, $propertyName, $value);
    }

    /**
     * @return array
     */
    public function dataNotSettable()
    {
        return [
            [new NoPropertiesObject(), 'foo', null],
            [new PrivatePropertyAndSetterObject(), 'foo', null],
            [new SettersWithNotOneArgumentObject(), 'foo', null],
            [new SettersWithNotOneArgumentObject(), 'bar', null],
        ];
    }

    /**
     * If all parameters are correct but the appropriate builder doesn't exist in the builders container,
     * the exception {@see \SilenceDis\ObjectBuilder\PropertySetter\PropertySetterExceptionInterface}
     * will be thrown.
     *
     * @covers ::set
     *
     * @throws \SilenceDis\ObjectBuilder\PropertySetter\PropertySetterException
     * @throws \SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException
     * @throws \TypeError
     */
    public function testSet_7()
    {
        $object = new TypeHintedPropertyObject();
        $propertyName = 'foo';
        $value = [];

        $buildersContainer = $this->getBuildersContainerMock(['methods' => ['has' => false]]);
        $setter = new SetPropertyThroughSetter($buildersContainer);

        $this->expectException(PropertySetterExceptionInterface::class);
        $setter->set($object, $propertyName, $value);
    }

    /**
     * If all parameters are correct and the appropriate builder exists in the builders container,
     * the appropriate builder must be used to build a value
     * and it's result must be passed to the property setter method.
     *
     * @covers ::set
     *
     * @throws \SilenceDis\ObjectBuilder\PropertySetter\PropertySetterException
     * @throws \SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException
     * @throws \TypeError
     */
    public function testSet_7_1()
    {
        $object = (new MockHelper($this))->mockObject(TypeHintedPropertyObject::class, ['methods' => ['setFoo']]);
        $propertyName = 'foo';
        $value = [];

        $buildResult = new \stdClass();
        $builder = (new MockHelper($this))->mockObject(
            BuilderInterface::class,
            [
                'mockType' => MockHelper::MOCK_TYPE_ABSTRACT,
                'methods' => [
                    'build' => $buildResult,
                ],
            ]
        );

        $buildersContainer = $this->getBuildersContainerMock(
            [
                'methods' => [
                    'has' => true,
                    'get' => $builder,
                ],
            ]
        );
        $setter = new SetPropertyThroughSetter($buildersContainer);

        $object->expects($this->once())->method('setFoo')->with($buildResult);
        $setter->set($object, $propertyName, $value);
    }

    /**
     * If the value type mathes with the type hint of parameter of setter,
     * this value will be set without any processing.
     *
     * @covers ::set
     *
     * @throws \SilenceDis\ObjectBuilder\PropertySetter\PropertySetterException
     * @throws \SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException
     * @throws \TypeError
     */
    public function testSet_8()
    {
        $object = (new MockHelper($this))->mockObject(TypeHintedPropertyObject::class, ['methods' => ['setFoo']]);
        $propertyName = 'foo';
        $setterMethodName = 'setFoo';
        $value = new \stdClass();

        $buildersContainer = $this->getBuildersContainerMock();
        $setter = new SetPropertyThroughSetter($buildersContainer);

        $object->expects($this->once())->method($setterMethodName)->with($value);
        $setter->set($object, $propertyName, $value);
    }

    # endregion

    # region canSet

    /**
     * The method "canSet" returns a boolean.
     * @covers       \SilenceDis\ObjectBuilder\PropertySetter\SetPropertyDirectly::canSet
     * @dataProvider dataCanSet
     *
     * @param $object
     * @param $propertyName
     * @param $expectedResult
     * @throws \SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException
     */
    public function testCanSet_1($object, $propertyName, $expectedResult)
    {
        $objectReflection = new \ReflectionClass($object);
        $setter = new SetPropertyThroughSetter($this->getBuildersContainerMock());
        $actualResult = $setter->canSet($objectReflection, $propertyName, null);
        $this->assertTrue(is_bool($actualResult));
    }

    /**
     * @covers ::canSet
     * @dataProvider dataCanSet
     *
     * @param $object
     * @param $propertyName
     * @param $value
     * @param $expectedResult
     * @throws \SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException
     */
    public function testCanSet_2($object, $propertyName, $value, $expectedResult)
    {
        $buildersContainer = $this->getBuildersContainerMock(['methods' => ['has']]);
        $buildersContainer->method('has')->willReturnMap(
            [
                [\stdClass::class, true],
                [ExampleInterface::class, false],
            ]
        );

        $objectReflection = new \ReflectionClass($object);
        $setter = new SetPropertyThroughSetter($buildersContainer);
        $actualResult = $setter->canSet($objectReflection, $propertyName, $value);
        $this->assertEquals($expectedResult, $actualResult);
    }


    public function dataCanSet()
    {
        return [
            // The object doesn't have any properties
            [new NoPropertiesObject(), 'foo', null, false],
            // The object doesn't have an appropriate public setter (actually, it's private).
            [new PrivatePropertyAndSetterObject(), 'foo', null, false],
            // The object has an appropriate public method that has parameter without type hinting
            [new PrivatePropertiesObject(), 'foo', null, true],
            // The object has an appropriate public method that has type hint of parameter but the null value is allowed
            [new TypeHintedButNotRequiredPropertyObject(), 'foo', null, true],
            // The value type matches with the type restriction of the setter method parameter
            [new TypeHintedPropertyObject(), 'foo', new \stdClass(), true],
            // The value type doesn't match with tye type restriction of the setter method parameter
            // but the builders container has a builder for the type
            [new TypeHintedPropertyObject(), 'foo', [], true],
            // The value type doesn't match with tye type restriction of the setter method parameter
            // and the builders container doesn't have a builder for the type
            [new TypeHintedPropertyObject(), 'bar', [], false],
            // The setter hasn't any parameters
            [new SettersWithNotOneArgumentObject(), 'foo', null, false],
            // The setter has more than one parameter
            [new SettersWithNotOneArgumentObject(), 'bar', null, false],
        ];
    }

    # endregion

    # region getSetterMethodName

    /**
     * @covers ::getSetterMethodName
     * @dataProvider dataGetSetterMethodName
     *
     * @param string $propertyName
     * @param string $expectedResult
     *
     * @throws \SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException
     * @throws \SilenceDis\ProtectedMembersAccessor\Exception\ProtectedMembersAccessException
     */
    public function testGetSetterMethodName($propertyName, $expectedResult)
    {
        $setter = (new MockHelper($this))->mockObject(SetPropertyThroughSetter::class, ['constructor' => false]);
        $closure = (new ProtectedMembersAccessor())->getProtectedMethod($setter, 'getSetterMethodName');
        $actualResult = $closure($propertyName);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return array
     */
    public function dataGetSetterMethodName()
    {
        return [
            ['foo', 'setFoo'],
            ['fooBar', 'setFooBar'],
            ['foo_bar', 'setFoo_bar'],
            ['FooBar', 'setFooBar'],
        ];
    }

    # endregion
}
