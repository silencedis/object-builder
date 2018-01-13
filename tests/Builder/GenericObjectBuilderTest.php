<?php

namespace SilenceDis\ObjectBuilder\Test\Builder;

use PHPUnit\Framework\TestCase;
use SilenceDis\ObjectBuilder\Builder\GenericObjectBuilder;
use SilenceDis\ObjectBuilder\BuildersContainer\BuildersContainerInterface;
use SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetterInterface;
use SilenceDis\PHPUnitMockHelper\MockHelper;
use SilenceDis\ProtectedMembersAccessor\ProtectedMembersAccessor;

class GenericObjectBuilderTest extends TestCase
{
    /**
     * @var MockHelper
     */
    private $mockHelper;

    /**
     * @return MockHelper
     */
    private function getMockHelper(): MockHelper
    {
        if ($this->mockHelper === null) {
            $this->mockHelper = new MockHelper($this);
        }

        return $this->mockHelper;
    }

    /**
     * If the "objectPrototype" parameter of the constructor isn't an object,
     * the {@see \TypeError} will be thrown.
     *
     * @covers       \SilenceDis\ObjectBuilder\Builder\GenericObjectBuilder::__construct
     *
     * @dataProvider dataInvalidObjectPrototypes
     *
     * @param mixed $objectProrotype
     * @throws \SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException
     */
    public function testConstructor_1($objectProrotype)
    {
        $this->expectException(\TypeError::class);
        /** @var BuildersContainerInterface $buildersContainer */
        $buildersContainer = (new MockHelper($this))->mockObject(
            BuildersContainerInterface::class,
            ['mockType' => MockHelper::MOCK_TYPE_ABSTRACT]
        );
        new GenericObjectBuilder($objectProrotype, $buildersContainer);
    }

    /**
     * @return array
     */
    public function dataInvalidObjectPrototypes()
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
     * The parameter "rawData" of the method "build" must be an array
     *
     * @dataProvider dataInvalidBuildParameters
     *
     * @param $invalidValue
     *
     * @throws \SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetterExceptionInterface
     * @throws \SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException
     * @throws \TypeError
     */
    public function testBuild_1($invalidValue)
    {
        $objectPrototype = new \stdClass();
        /** @var BuildersContainerInterface $buildersContainer */
        $buildersContainer = $this->getMockHelper()->mockObject(
            BuildersContainerInterface::class,
            ['mockType' => MockHelper::MOCK_TYPE_ABSTRACT]
        );
        $builder = new GenericObjectBuilder($objectPrototype, $buildersContainer);

        $this->expectException(\TypeError::class);
        $builder->build($invalidValue);
    }

    /**
     * @return array
     */
    public function dataInvalidBuildParameters()
    {
        return [
            [1],
            [1.1],
            [null],
            [new \stdClass()],
            ['string'],
        ];
    }

    /**
     * @covers       \SilenceDis\ObjectBuilder\Builder\GenericObjectBuilder::build
     *
     * @dataProvider dataRawForBuild
     *
     * @param $rawData
     * @param $expectedCallsNumber
     *
     * @throws \SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetterExceptionInterface
     * @throws \SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException
     * @throws \SilenceDis\ProtectedMembersAccessor\Exception\ProtectedMembersAccessException
     * @throws \TypeError
     */
    public function testBuild_2($rawData, $expectedCallsNumber)
    {
        $propertiesSetter = $this->getMockHelper()->mockObject(
            PropertiesSetterInterface::class,
            ['mockType' => MockHelper::MOCK_TYPE_ABSTRACT]
        );

        /** @var \PHPUnit_Framework_MockObject_MockObject|GenericObjectBuilder $builder */
        $builder = $this->getMockHelper()->mockObject(
            GenericObjectBuilder::class,
            [
                'methods' => [
                    'createPropertiesSetter' => $propertiesSetter,
                ],
            ]
        );
        (new ProtectedMembersAccessor())->setProtectedProperty(
            GenericObjectBuilder::class,
            $builder,
            'objectPrototype',
            new \stdClass()
        );
        $propertiesSetter->expects($this->exactly($expectedCallsNumber))->method('set');
        $builder->build($rawData);
    }

    /**
     * @return array
     */
    public function dataRawForBuild()
    {
        return [
            [
                ['property1' => 'value1'],
                1,
            ],
            [
                ['property1' => 'value1', 'property2' => 'value2'],
                2,
            ],
            [
                ['property1' => 'value1', 'property2' => 'value2', 'property3' => 'value3'],
                3,
            ],
        ];
    }

    /**
     * @covers \SilenceDis\ObjectBuilder\Builder\GenericObjectBuilder::build
     *
     * @throws \SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetterExceptionInterface
     * @throws \SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException
     * @throws \TypeError
     */
    public function testBuild_3()
    {
        $objectPrototype = new \stdClass();
        $expectedClass = get_class($objectPrototype);
        $rawData = [];
        /** @var BuildersContainerInterface $buildersContainer */
        $buildersContainer = $this->getMockHelper()->mockObject(
            BuildersContainerInterface::class,
            ['mockType' => MockHelper::MOCK_TYPE_ABSTRACT]
        );

        $builder = new GenericObjectBuilder($objectPrototype, $buildersContainer);
        $result = $builder->build($rawData);
        $this->assertInstanceOf($expectedClass, $result);
    }

    /**
     * @throws \SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException
     * @throws \SilenceDis\ProtectedMembersAccessor\Exception\ProtectedMembersAccessException
     */
    public function testCreatePropertiesSetter()
    {
        $objectPrototype = new \stdClass();
        $expectedClass = PropertiesSetterInterface::class;
        /** @var BuildersContainerInterface $buildersContainer */
        $buildersContainer = $this->getMockHelper()->mockObject(
            BuildersContainerInterface::class,
            ['mockType' => MockHelper::MOCK_TYPE_ABSTRACT]
        );
        $builder = new GenericObjectBuilder($objectPrototype, $buildersContainer);
        $closure = (new ProtectedMembersAccessor())->getProtectedMethod($builder, 'createPropertiesSetter');
        $result = $closure(clone($objectPrototype));
        $this->assertInstanceOf($expectedClass, $result);
    }
}
