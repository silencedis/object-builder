<?php

namespace SilenceDis\ObjectBuilder\Test\Builder;

use PHPUnit\Framework\TestCase;
use SilenceDis\ObjectBuilder\Builder\GenericObjectBuilder;
use SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetterInterface;
use SilenceDis\PHPUnitMockHelper\MockHelper;

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
     * @param array $mockConfig
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|PropertiesSetterInterface
     *
     * @throws \SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException
     */
    private function getPropertiesSetterMock($mockConfig = [])
    {
        return $this->getMockHelper()->mockObject(
            PropertiesSetterInterface::class,
            ['mockType' => MockHelper::MOCK_TYPE_ABSTRACT],
            $mockConfig
        );
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
        $propertiesSetter = $this->getPropertiesSetterMock();
        $this->expectException(\TypeError::class);
        new GenericObjectBuilder($objectProrotype, $propertiesSetter);
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
     * @throws \SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetterException
     * @throws \SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetterExceptionInterface
     * @throws \SilenceDis\ObjectBuilder\PropertySetter\PropertySetterExceptionInterface
     * @throws \SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException
     * @throws \TypeError
     */
    public function testBuild_1($invalidValue)
    {
        $objectPrototype = new \stdClass();
        $propertiesSetter = $this->getPropertiesSetterMock();
        $builder = new GenericObjectBuilder($objectPrototype, $propertiesSetter);

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
     * @covers \SilenceDis\ObjectBuilder\Builder\GenericObjectBuilder::build
     *
     * @throws \SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetterException
     * @throws \SilenceDis\ObjectBuilder\PropertySetter\PropertySetterExceptionInterface
     * @throws \SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException
     * @throws \TypeError
     * @throws \SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetterExceptionInterface
     */
    public function testBuild_2()
    {
        $propertiesSetter = $this->getPropertiesSetterMock();
        $objectPrototype = new \stdClass();
        $expectedClass = get_class($objectPrototype);
        $rawData = [];

        $builder = new GenericObjectBuilder($objectPrototype, $propertiesSetter);
        $result = $builder->build($rawData);
        $this->assertInstanceOf($expectedClass, $result);
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
     * @covers       \SilenceDis\ObjectBuilder\Builder\GenericObjectBuilder::build
     *
     * @dataProvider dataRawForBuild
     *
     * @param $rawData
     * @param $expectedCallsNumber
     *
     * @throws \SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetterException
     * @throws \SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetterExceptionInterface
     * @throws \SilenceDis\ObjectBuilder\PropertySetter\PropertySetterExceptionInterface
     * @throws \SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException
     * @throws \TypeError
     */
    public function testBuild_3($rawData, $expectedCallsNumber)
    {
        $propertiesSetter = $this->getPropertiesSetterMock(['methods' => ['set']]);
        $objectPrototype = new \stdClass();
        $expectedClass = get_class($objectPrototype);

        $propertiesSetter->expects($this->exactly($expectedCallsNumber))->method('set');

        $builder = new GenericObjectBuilder($objectPrototype, $propertiesSetter);
        $result = $builder->build($rawData);
        $this->assertInstanceOf($expectedClass, $result);
    }
}
