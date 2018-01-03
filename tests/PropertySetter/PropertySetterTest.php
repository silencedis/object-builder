<?php

namespace SilenceDis\ObjectBuilder\Test\PropertySetter;

use PHPUnit\Framework\TestCase;
use SilenceDis\ObjectBuilder\Builder\BuilderInterface;
use SilenceDis\ObjectBuilder\BuildersContainer\BuilderNotFoundExceptionInterface;
use SilenceDis\ObjectBuilder\BuildersContainer\BuildersContainerInterface;
use SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetter;
use SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetterException;
use SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetterExceptionInterface;
use SilenceDis\ObjectBuilder\Test\Fixture\PrivatePropertiesObject;
use SilenceDis\ObjectBuilder\Test\Fixture\PrivatePropertyAndSetterObject;
use SilenceDis\ObjectBuilder\Test\Fixture\PublicPropertiesObject;
use SilenceDis\ObjectBuilder\Test\Fixture\PublicPropertyWithSetterObject;
use SilenceDis\ObjectBuilder\Test\Fixture\SettersWithNotOneArgumentObject;
use SilenceDis\ObjectBuilder\Test\Fixture\TypeHintedButNotRequiredPropertyObject;
use SilenceDis\ObjectBuilder\Test\Fixture\TypeHintedPropertyObject;
use SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException;
use SilenceDis\PHPUnitMockHelper\MockHelper;

class PropertySetterTest extends TestCase
{
    /**
     * @param array $mockConfig
     * @return \PHPUnit_Framework_MockObject_MockObject|BuildersContainerInterface
     * @throws InvalidMockTypeException
     */
    private function getBuildersContainerMock($mockConfig = [])
    {
        $buildersContainer = (new MockHelper($this))->mockObject(
            BuildersContainerInterface::class,
            [
                'mockType' => MockHelper::MOCK_TYPE_ABSTRACT,
            ],
            $mockConfig
        );

        return $buildersContainer;
    }

    /**
     * Set non-marked public property.
     *
     * @covers       \SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetter::set
     *
     * @dataProvider dataSetNonMarkedPublicProperty
     * @param $value
     * @throws BuilderNotFoundExceptionInterface
     * @throws PropertiesSetterException
     */
    public function testSet_1($value)
    {
        $object = new PublicPropertiesObject();
        $setter = new PropertiesSetter($object);

        $propertyName = 'foo';
        $setter->set($propertyName, $value);
        $this->assertTrue($object->{$propertyName} === $value, 'The property must be set as is.');
    }

    /**
     * @return array
     */
    public function dataSetNonMarkedPublicProperty()
    {
        return [
            ['string value'],
            [1],
            [1.1],
            [false],
            [new \stdClass()],
            [new \ArrayObject(['foo', 'bar'])],
            [[]],
        ];
    }

    /**
     * When the property is private and a setter exists, the setter must be called.
     *
     * @covers \SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetter::set
     * @throws BuilderNotFoundExceptionInterface
     * @throws PropertiesSetterException
     * @throws InvalidMockTypeException
     */
    public function testSet_2()
    {
        $testPropertyName = 'property1';
        $testPropertySetter = 'setProperty1';
        $testValue = 'test string'; // The value type doesn't matter in this test

        $buildersContainer = $this->getBuildersContainerMock();

        $object = (new MockHelper($this))->mockObject(
            PrivatePropertiesObject::class,
            ['methods' => [$testPropertySetter]]
        );

        $setter = new PropertiesSetter($object, $buildersContainer);

        $object->expects($this->once())->method($testPropertySetter)->with($testValue);

        $setter->set($testPropertyName, $testValue);
    }

    /**
     * If the property is public and the public setter exists, the setter won't be used.
     * The property will be set directly.
     *
     * @covers \SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetter::set
     * @throws BuilderNotFoundExceptionInterface
     * @throws PropertiesSetterException
     * @throws InvalidMockTypeException
     */
    public function testSet_3()
    {
        $testPropertyName = 'foo';
        $testPropertySetter = 'setFoo';
        $testPropertyValue = 'test string'; // The value doesn't matter in this test

        $buildersContainer = $this->getBuildersContainerMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|PublicPropertyWithSetterObject $object */
        $object = (new MockHelper($this))->mockObject(
            PublicPropertyWithSetterObject::class,
            ['methods' => [$testPropertySetter]]
        );
        $setter = new PropertiesSetter($object, $buildersContainer);

        $object->expects($this->never())->method($testPropertySetter);
        $setter->set($testPropertyName, $testPropertyValue);
        $this->assertTrue($object->foo === $testPropertyValue, 'The test public property must be set.');
    }

    /**
     * If the property and its setter are private, the exception must be thrown.
     *
     * @covers \SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetter::set
     *
     * @throws BuilderNotFoundExceptionInterface
     * @throws PropertiesSetterException
     * @throws InvalidMockTypeException
     */
    public function testSet_4()
    {
        $testPropertyName = 'foo';
        $testPropertySetter = 'setFoo';
        $testPropertyValue = 'test string'; // The value doesn't matter in this test

        $buildersContainer = $this->getBuildersContainerMock();

        /** @var \PHPUnit_Framework_MockObject_MockObject|PrivatePropertiesObject $object */
        $object = (new MockHelper($this))->mockObject(
            PrivatePropertyAndSetterObject::class,
            ['methods' => [$testPropertySetter]]
        );

        $setter = new PropertiesSetter($object, $buildersContainer);
        $this->expectException(PropertiesSetterExceptionInterface::class);

        $setter->set($testPropertyName, $testPropertyValue);
    }

    /**
     * If the setter doesn't have any arguments, the exception must be thrown.
     *
     * @covers \SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetter::set
     *
     * @throws BuilderNotFoundExceptionInterface
     * @throws PropertiesSetterException
     * @throws InvalidMockTypeException
     */
    public function testSet_5()
    {
        $testPropertyName = 'foo';
        $testPropertyValue = 'test string';

        $buildersContainer = $this->getBuildersContainerMock();
        $object = new SettersWithNotOneArgumentObject();
        $setter = new PropertiesSetter($object, $buildersContainer);

        $this->expectException(PropertiesSetterException::class);
        $this->expectExceptionMessage('Setters must have one parameter.');
        $setter->set($testPropertyName, $testPropertyValue);
    }

    /**
     * If the setter have more than one argument, the exception must be thrown.
     *
     * @covers \SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetter::set
     *
     * @throws BuilderNotFoundExceptionInterface
     * @throws PropertiesSetterException
     * @throws InvalidMockTypeException
     */
    public function testSet_6()
    {
        $testPropertyName = 'bar';
        $testPropertyValue = 'test string';

        $buildersContainer = $this->getBuildersContainerMock();
        $object = new SettersWithNotOneArgumentObject();
        $setter = new PropertiesSetter($object, $buildersContainer);

        $this->expectException(PropertiesSetterException::class);
        $this->expectExceptionMessage('Setters must have one parameter.');
        $setter->set($testPropertyName, $testPropertyValue);
    }

    // есть тип параметра, но он необязательный и значение - null

    /**
     * If the argument of setter is type-hinted but the `null` value is allowed,
     * the `null` value will be set.
     *
     * @covers \SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetter::set
     * @throws BuilderNotFoundExceptionInterface
     * @throws PropertiesSetterException
     * @throws InvalidMockTypeException
     */
    public function testSet_7()
    {
        $testPropertyName = 'foo';
        $testPropertySetter = 'setFoo';
        $testPropertyValue = null;

        $buildersContainer = $this->getBuildersContainerMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|TypeHintedButNotRequiredPropertyObject $object */
        $object = (new MockHelper($this))->mockObject(
            TypeHintedButNotRequiredPropertyObject::class,
            ['methods' => [$testPropertySetter]]
        );
        $setter = new PropertiesSetter($object, $buildersContainer);

        $object->expects($this->once())->method($testPropertySetter)->with($testPropertyValue);

        $setter->set($testPropertyName, $testPropertyValue);
    }

    /**
     * If the type of value matches with the type hint,
     * the value will be passed to the setter as is.
     * Any object builder won't be used.
     *
     * @covers \SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetter::set
     * @throws BuilderNotFoundExceptionInterface
     * @throws PropertiesSetterException
     * @throws InvalidMockTypeException
     */
    public function testSet_8()
    {
        $testPropertyName = 'foo';
        $testPropertySetter = 'setFoo';
        $testPropertyValue = new \stdClass();

        $buildersContainer = $this->getBuildersContainerMock(
            [
                'methods' => ['has'],
            ]
        );
        /** @var \PHPUnit_Framework_MockObject_MockObject|TypeHintedPropertyObject $object */
        $object = (new MockHelper($this))->mockObject(
            TypeHintedPropertyObject::class,
            ['methods' => [$testPropertySetter]]
        );
        $setter = new PropertiesSetter($object, $buildersContainer);

        $buildersContainer->expects($this->never())->method('has');

        $setter->set($testPropertyName, $testPropertyValue);
    }

    /**
     * @throws BuilderNotFoundExceptionInterface
     * @throws PropertiesSetterException
     * @throws InvalidMockTypeException
     */
    public function testSet_9()
    {
        $testPropertyName = 'foo';
        $testPropertySetter = 'setFoo';
        $testPropertyValue = [];
        $testBuiltPropertyValue = new \stdClass();

        $testBuilder = (new MockHelper($this))->mockObject(
            BuilderInterface::class,
            [
                'mockType' => MockHelper::MOCK_TYPE_ABSTRACT,
                'methods' => [
                    'build' => $testBuiltPropertyValue,
                ],
            ]
        );
        $buildersContainer = $this->getBuildersContainerMock(
            [
                'methods' => [
                    'has' => true,
                    'get' => $testBuilder,
                ],
            ]
        );

        /** @var \PHPUnit_Framework_MockObject_MockObject|TypeHintedPropertyObject $object */
        $object = (new MockHelper($this))->mockObject(
            TypeHintedPropertyObject::class,
            ['methods' => [$testPropertySetter]]
        );
        $setter = new PropertiesSetter($object, $buildersContainer);

        $buildersContainer->expects($this->atLeast(1))->method('has')->with(\stdClass::class);
        $buildersContainer->expects($this->atLeast(1))->method('get');
        $testBuilder->expects($this->once())->method('build')->with($testPropertyValue);

        $setter->set($testPropertyName, $testPropertyValue);
    }

    /**
     * @throws BuilderNotFoundExceptionInterface
     * @throws PropertiesSetterException
     * @throws InvalidMockTypeException
     */
    public function testSet_10()
    {
        $testPropertyName = 'foo';
        $testPropertySetter = 'setFoo';
        $testPropertyValue = [];
        $testBuiltPropertyValue = new \stdClass();

        $testBuilder = (new MockHelper($this))->mockObject(
            BuilderInterface::class,
            [
                'mockType' => MockHelper::MOCK_TYPE_ABSTRACT,
                'methods' => [
                    'build' => $testBuiltPropertyValue,
                ],
            ]
        );
        $buildersContainer = $this->getBuildersContainerMock(
            [
                'methods' => [
                    'has' => false,
                    'get' => $testBuilder,
                ],
            ]
        );

        /** @var \PHPUnit_Framework_MockObject_MockObject|TypeHintedPropertyObject $object */
        $object = (new MockHelper($this))->mockObject(
            TypeHintedPropertyObject::class,
            ['methods' => [$testPropertySetter]]
        );
        $setter = new PropertiesSetter($object, $buildersContainer);

        $buildersContainer->expects($this->atLeast(1))->method('has')->with(\stdClass::class);

        $this->expectException(PropertiesSetterExceptionInterface::class);

        $setter->set($testPropertyName, $testPropertyValue);
    }
}
