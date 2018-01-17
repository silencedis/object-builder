<?php

namespace SilenceDis\ObjectBuilder\Test\ObjectPropertiesSetter;

use PHPUnit\Framework\TestCase;
use SilenceDis\ObjectBuilder\Builder\BuilderInterface;
use SilenceDis\ObjectBuilder\BuildersContainer\BuildersContainerInterface;
use SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetter;
use SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetterException;
use SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetterExceptionInterface;
use SilenceDis\ObjectBuilder\PropertySetter\PropertySetterInterface;
use SilenceDis\ObjectBuilder\PropertySetter\SetPropertyDirectly;
use SilenceDis\ObjectBuilder\PropertySetter\SetPropertyThroughSetter;
use SilenceDis\ObjectBuilder\Test\Fixture\PrivatePropertiesObject;
use SilenceDis\ObjectBuilder\Test\Fixture\PrivatePropertyAndSetterObject;
use SilenceDis\ObjectBuilder\Test\Fixture\PublicPropertiesObject;
use SilenceDis\ObjectBuilder\Test\Fixture\PublicPropertyWithSetterObject;
use SilenceDis\ObjectBuilder\Test\Fixture\SettersWithNotOneArgumentObject;
use SilenceDis\ObjectBuilder\Test\Fixture\TypeHintedButNotRequiredPropertyObject;
use SilenceDis\ObjectBuilder\Test\Fixture\TypeHintedPropertyObject;
use SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException;
use SilenceDis\PHPUnitMockHelper\MockHelper;

/**
 * Class PropertiesSetterTest
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 *
 * @coversDefaultClass \SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetter
 */
class PropertiesSetterTest extends TestCase
{
    /**
     * @var MockHelper
     */
    private $mockHelper = null;

    /**
     * @return MockHelper
     */
    private function getMockHelper()
    {
        if ($this->mockHelper === null) {
            $this->mockHelper = new MockHelper($this);
        }

        return $this->mockHelper;
    }

    /**
     * @param array $mockConfig
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|BuildersContainerInterface
     *
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
     * @return array
     *
     * @throws InvalidMockTypeException
     */
    private function getPropertySetters()
    {
        $buildersContainer = $this->getBuildersContainerMock();

        return [
            new SetPropertyDirectly(),
            new SetPropertyThroughSetter($buildersContainer),
        ];
    }

    /**
     * If the "object" parameter isn't an object, the {@see \TypeError} will be thown.
     *
     * @covers ::__construct
     * @dataProvider dataInvalidObjects
     *
     * @param mixed $object
     */
    public function testConstructor_1($object)
    {
        $this->expectException(\TypeError::class);
        new PropertiesSetter($object, []);
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
            [false],
            [true],
        ];
    }

    /**
     * If at least one of property setters is invalid,
     * the {@see \SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetterExceptionInterface} will be thrown.
     *
     * @covers ::__construct
     * @dataProvider dataInvalidPropertySetters
     *
     * @param $propertySetters
     */
    public function testConstructor_2($propertySetters)
    {
        $this->expectException(PropertiesSetterExceptionInterface::class);
        new PropertiesSetter(new \stdClass, $propertySetters);
    }

    /**
     * @return array
     *
     * @throws InvalidMockTypeException
     */
    public function dataInvalidPropertySetters()
    {
        $mockHelper = new MockHelper($this);
        $validPropertySetter = $mockHelper->mockObject(SetPropertyDirectly::class, ['constructor' => false]);
        $invalidPropertySetter = \stdClass::class;

        return [
            [[$validPropertySetter, $invalidPropertySetter]],
            [[$invalidPropertySetter, $validPropertySetter]],
        ];
    }

    /**
     * Set non-marked public property.
     *
     * @covers ::set
     * @covers ::__construct
     * @dataProvider dataSetNonMarkedPublicProperty
     *
     * @param $value
     *
     * @throws InvalidMockTypeException
     * @throws PropertiesSetterException
     * @throws \SilenceDis\ObjectBuilder\PropertySetter\PropertySetterExceptionInterface
     * @throws \TypeError
     */
    public function testSet_1($value)
    {
        $object = new PublicPropertiesObject();
        $setter = new PropertiesSetter($object, $this->getPropertySetters());

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
     * @covers ::set
     * @covers ::__construct
     *
     * @throws InvalidMockTypeException
     * @throws PropertiesSetterException
     * @throws \SilenceDis\ObjectBuilder\PropertySetter\PropertySetterExceptionInterface
     * @throws \TypeError
     */
    public function testSet_2()
    {
        $testPropertyName = 'foo';
        $testPropertySetter = 'setFoo';
        $testValue = 'test string'; // The value type doesn't matter in this test

        $object = (new MockHelper($this))->mockObject(
            PrivatePropertiesObject::class,
            ['methods' => [$testPropertySetter]]
        );

        $setter = new PropertiesSetter($object, $this->getPropertySetters());

        $object->expects($this->once())->method($testPropertySetter)->with($testValue);

        $setter->set($testPropertyName, $testValue);
    }

    /**
     * If the property is public and the public setter exists, the setter won't be used.
     * The property will be set directly.
     *
     * @covers ::set
     * @covers ::__construct
     *
     * @throws InvalidMockTypeException
     * @throws PropertiesSetterException
     * @throws \SilenceDis\ObjectBuilder\PropertySetter\PropertySetterExceptionInterface
     * @throws \TypeError
     */
    public function testSet_3()
    {
        $testPropertyName = 'foo';
        $testPropertySetter = 'setFoo';
        $testPropertyValue = 'test string'; // The value doesn't matter in this test

        /** @var \PHPUnit_Framework_MockObject_MockObject|PublicPropertyWithSetterObject $object */
        $object = (new MockHelper($this))->mockObject(
            PublicPropertyWithSetterObject::class,
            ['methods' => [$testPropertySetter]]
        );
        $setter = new PropertiesSetter($object, $this->getPropertySetters());

        $object->expects($this->never())->method($testPropertySetter);
        $setter->set($testPropertyName, $testPropertyValue);
        $this->assertTrue($object->foo === $testPropertyValue, 'The test public property must be set.');
    }

    /**
     * If the property and its setter are private, the exception must be thrown.
     *
     * @covers ::set
     * @covers ::__construct
     *
     * @throws InvalidMockTypeException
     * @throws PropertiesSetterException
     * @throws \SilenceDis\ObjectBuilder\PropertySetter\PropertySetterExceptionInterface
     * @throws \TypeError
     */
    public function testSet_4()
    {
        $testPropertyName = 'foo';
        $testPropertySetter = 'setFoo';
        $testPropertyValue = 'test string'; // The value doesn't matter in this test

        /** @var \PHPUnit_Framework_MockObject_MockObject|PrivatePropertiesObject $object */
        $object = (new MockHelper($this))->mockObject(
            PrivatePropertyAndSetterObject::class,
            ['methods' => [$testPropertySetter]]
        );

        $setter = new PropertiesSetter($object, $this->getPropertySetters());
        $this->expectException(PropertiesSetterExceptionInterface::class);

        $setter->set($testPropertyName, $testPropertyValue);
    }

    /**
     * If the setter doesn't have any arguments, the exception must be thrown.
     *
     * @covers ::set
     * @covers ::__construct
     *
     * @throws InvalidMockTypeException
     * @throws PropertiesSetterException
     * @throws \SilenceDis\ObjectBuilder\PropertySetter\PropertySetterExceptionInterface
     * @throws \TypeError
     */
    public function testSet_5()
    {
        $testPropertyName = 'foo';
        $testPropertyValue = 'test string';

        $object = new SettersWithNotOneArgumentObject();
        $setter = new PropertiesSetter($object, $this->getPropertySetters());

        $this->expectException(PropertiesSetterException::class);
        $setter->set($testPropertyName, $testPropertyValue);
    }

    /**
     * If the setter have more than one argument, the exception must be thrown.
     *
     * @covers ::set
     * @covers ::__construct
     *
     * @throws InvalidMockTypeException
     * @throws PropertiesSetterException
     * @throws \SilenceDis\ObjectBuilder\PropertySetter\PropertySetterExceptionInterface
     * @throws \TypeError
     */
    public function testSet_6()
    {
        $testPropertyName = 'bar';
        $testPropertyValue = 'test string';

        $object = new SettersWithNotOneArgumentObject();
        $setter = new PropertiesSetter($object, $this->getPropertySetters());

        $this->expectException(PropertiesSetterException::class);
        $setter->set($testPropertyName, $testPropertyValue);
    }

    /**
     * If the argument of setter is type-hinted but the `null` value is allowed,
     * the `null` value will be set.
     *
     * @covers ::set
     * @covers ::__construct
     *
     * @throws InvalidMockTypeException
     * @throws PropertiesSetterException
     * @throws \SilenceDis\ObjectBuilder\PropertySetter\PropertySetterExceptionInterface
     * @throws \TypeError
     */
    public function testSet_7()
    {
        $testPropertyName = 'foo';
        $testPropertySetter = 'setFoo';
        $testPropertyValue = null;

        /** @var \PHPUnit_Framework_MockObject_MockObject|TypeHintedButNotRequiredPropertyObject $object */
        $object = (new MockHelper($this))->mockObject(
            TypeHintedButNotRequiredPropertyObject::class,
            ['methods' => [$testPropertySetter]]
        );
        $setter = new PropertiesSetter($object, $this->getPropertySetters());

        $object->expects($this->once())->method($testPropertySetter)->with($testPropertyValue);

        $setter->set($testPropertyName, $testPropertyValue);
    }

    /**
     * If the type of value matches with the type hint,
     * the value will be passed to the setter as is.
     * Any object builder won't be used.
     *
     * @covers ::set
     * @covers ::__construct
     *
     * @throws InvalidMockTypeException
     * @throws PropertiesSetterException
     * @throws \SilenceDis\ObjectBuilder\PropertySetter\PropertySetterExceptionInterface
     * @throws \TypeError
     */
    public function testSet_8()
    {
        $testPropertyName = 'foo';
        $testPropertySetter = 'setFoo';
        $testPropertyValue = new \stdClass();

        /** @var \PHPUnit_Framework_MockObject_MockObject|TypeHintedPropertyObject $object */
        $object = (new MockHelper($this))->mockObject(
            TypeHintedPropertyObject::class,
            ['methods' => [$testPropertySetter]]
        );

        $buildersContainer = $this->getBuildersContainerMock(
            [
                'methods' => ['has'],
            ]
        );
        $propertySetters = [
            new SetPropertyDirectly(),
            new SetPropertyThroughSetter($buildersContainer),
        ];
        $setter = new PropertiesSetter($object, $propertySetters);

        $buildersContainer->expects($this->never())->method('has');

        $setter->set($testPropertyName, $testPropertyValue);
    }

    /**
     * @covers ::set
     * @covers ::__construct
     *
     * @throws InvalidMockTypeException
     * @throws PropertiesSetterException
     * @throws \SilenceDis\ObjectBuilder\PropertySetter\PropertySetterExceptionInterface
     * @throws \TypeError
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
        $propertySetters = [
            new SetPropertyDirectly(),
            new SetPropertyThroughSetter($buildersContainer),
        ];

        /** @var \PHPUnit_Framework_MockObject_MockObject|TypeHintedPropertyObject $object */
        $object = (new MockHelper($this))->mockObject(
            TypeHintedPropertyObject::class,
            ['methods' => [$testPropertySetter]]
        );
        $setter = new PropertiesSetter($object, $propertySetters);

        $buildersContainer->expects($this->atLeast(1))->method('has')->with(\stdClass::class);
        $buildersContainer->expects($this->atLeast(1))->method('get');
        $testBuilder->expects($this->once())->method('build')->with($testPropertyValue);

        $setter->set($testPropertyName, $testPropertyValue);
    }

    /**
     * @covers ::set
     * @covers ::__construct
     *
     * @throws InvalidMockTypeException
     * @throws PropertiesSetterException
     * @throws \SilenceDis\ObjectBuilder\PropertySetter\PropertySetterExceptionInterface
     * @throws \TypeError
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
        $propertySetters = [
            new SetPropertyDirectly(),
            new SetPropertyThroughSetter($buildersContainer),
        ];

        /** @var \PHPUnit_Framework_MockObject_MockObject|TypeHintedPropertyObject $object */
        $object = (new MockHelper($this))->mockObject(
            TypeHintedPropertyObject::class,
            ['methods' => [$testPropertySetter]]
        );
        $setter = new PropertiesSetter($object, $propertySetters);

        $buildersContainer->expects($this->atLeast(1))->method('has')->with(\stdClass::class);

        $this->expectException(PropertiesSetterExceptionInterface::class);

        $setter->set($testPropertyName, $testPropertyValue);
    }

    /**
     * @covers ::set
     * @covers ::throwPropertiesSetterException
     * @dataProvider dataAppropriateExceptions
     *
     * @param $testException
     *
     * @throws InvalidMockTypeException
     * @throws \SilenceDis\ObjectBuilder\PropertySetter\PropertySetterExceptionInterface
     * @throws \TypeError
     */
    public function testSet_11($testException)
    {
        $setter = $this->prepareSetterForExceptionHandlingTests($testException);

        try {
            // The propertyName and value don't matter in this test
            $setter->set('foo', 'bar');
        } catch (PropertiesSetterException $e) {
            $this->assertTrue($e->getPrevious() === $testException);

            return;
        }

        $this->fail();
    }

    /**
     * @return array
     */
    public function dataAppropriateExceptions()
    {
        return [
            [new \Exception()],
            [new \TypeError()],
        ];
    }

    /**
     * All the rest exception won't be intercepted
     *
     * @covers ::set
     * @dataProvider dataNotAppropriateExceptions
     *
     * @param $testException
     *
     * @throws InvalidMockTypeException
     */
    public function testSet_12($testException)
    {
        $setter = $this->prepareSetterForExceptionHandlingTests($testException);
        try {
            // The propertyName and value don't matter in this test
            $setter->set('foo', 'bar');
        } catch (\Throwable $e) {
            $this->assertTrue($e === $testException);

            return;
        }

        // If any exception wasn't catched, the test is considered as failed
        $this->fail();
    }

    /**
     * @return array
     */
    public function dataNotAppropriateExceptions()
    {
        return [
            [new \Error()],
            [new \ParseError()],
        ];
    }

    /**
     * @param \Throwable $testException
     *
     * @return PropertiesSetter
     *
     * @throws InvalidMockTypeException
     */
    private function prepareSetterForExceptionHandlingTests($testException)
    {
        $object = new \stdClass();
        $propertySetter = $this->getMockHelper()->mockObject(
            PropertySetterInterface::class,
            [
                'mockType' => MockHelper::MOCK_TYPE_ABSTRACT,
                'methods' => [
                    'canSet' => true,
                    'set',
                ],
                'will' => [
                    'set' => $this->returnCallback(
                        function () use ($testException) {
                            throw $testException;
                        }
                    ),
                ],
            ]
        );
        // The propertySetters array contains only one item - mocked property setter
        // to ensure that all will wark expectedly
        $setter = new PropertiesSetter($object, [$propertySetter]);

        return $setter;
    }
}
