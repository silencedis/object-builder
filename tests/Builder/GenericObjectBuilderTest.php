<?php

namespace SilenceDis\ObjectBuilder\Test\Builder;

use PHPUnit\Framework\TestCase;
use SilenceDis\ObjectBuilder\Builder\GenericObjectBuilder;
use SilenceDis\ObjectBuilder\BuildersContainer\BuildersContainerInterface;
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
     * The parameter "rawData" of the method "build" must be an array
     *
     * @dataProvider dataInvalidBuildParameters
     *
     * @param $invalidValue
     *
     * @throws \SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetterException
     * @throws \SilenceDis\ObjectBuilder\PropertySetter\PropertySetterExceptionInterface
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
}
