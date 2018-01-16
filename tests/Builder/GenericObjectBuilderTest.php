<?php

namespace SilenceDis\ObjectBuilder\Test\Builder;

use PHPUnit\Framework\TestCase;
use SilenceDis\ObjectBuilder\Builder\GenericObjectBuilder;
use SilenceDis\ObjectBuilder\BuildersContainer\BuildersContainerInterface;
use SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetterException;
use SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetterInterface;
use SilenceDis\ObjectBuilder\Test\Fixture\ExceptionHandler;
use SilenceDis\PHPUnitMockHelper\MockHelper;
use SilenceDis\ProtectedMembersAccessor\ProtectedMembersAccessor;

/**
 * Class GenericObjectBuilderTest
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 *
 * @coversDefaultClass \SilenceDis\ObjectBuilder\Builder\GenericObjectBuilder
 */
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
     * @covers ::__construct
     * @dataProvider dataInvalidObjectPrototypes
     *
     * @param mixed $objectProrotype
     *
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
     * @covers ::build
     * @covers ::__construct
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
     * @covers       ::build
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
     * @covers ::build
     * @covers ::__construct
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
     * If the PropertiesSetterExceptionInterface exception is thrown and a properties setter exception handler is set,
     * the handler will be called.
     *
     * @covers ::build
     * @covers ::__construct
     * @dataProvider dataForPropertiesExceptionHandlingTesting
     *
     * @param PropertiesSetterException $exception
     * @param PropertiesSetterInterface $propertiesSetter
     * @param $objectPrototype
     * @param BuildersContainerInterface $buildersContainer
     *
     * @throws \SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetterExceptionInterface
     * @throws \SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException
     * @throws \TypeError
     */
    public function testBuild_4(
        PropertiesSetterException $exception,
        PropertiesSetterInterface $propertiesSetter,
        $objectPrototype,
        BuildersContainerInterface $buildersContainer
    ) {
        $exceptionHandler = $this->getMockHelper()->mockObject(ExceptionHandler::class, ['methods' => ['__invoke']]);

        /** @var GenericObjectBuilder $builder */
        $builder = $this->getMockHelper()->mockObject(
            GenericObjectBuilder::class,
            [
                'constructor' => true,
                'constructorArgs' => [$objectPrototype, $buildersContainer, $exceptionHandler],
                'methods' => [
                    'createPropertiesSetter' => $propertiesSetter,
                ],
            ]
        );
        // Due to the fact that the rawData array contains only one item
        // and the method "set" of PropertiesSetter mocked to always throw an exception,
        // the handler call is expected only once.
        $rawData = ['foo' => 'bar'];
        $exceptionHandler->expects($this->once())->method('__invoke')->with($exception);
        $builder->build($rawData);
    }

    /**
     * If the PropertiesSetterExceptionInterface exception is thrown
     * and a properties setter exception handler is not set through the builder constructor,
     * the thrown exception will be re-thrown.
     *
     * @covers ::build
     * @covers ::__construct
     * @dataProvider dataForPropertiesExceptionHandlingTesting
     *
     * @param PropertiesSetterException $exception
     * @param PropertiesSetterInterface $propertiesSetter
     * @param $objectPrototype
     * @param BuildersContainerInterface $buildersContainer
     *
     * @throws \SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetterExceptionInterface
     * @throws \SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException
     * @throws \TypeError
     */
    public function testBuild_5(
        PropertiesSetterException $exception,
        PropertiesSetterInterface $propertiesSetter,
        $objectPrototype,
        BuildersContainerInterface $buildersContainer
    ) {
        /** @var GenericObjectBuilder $builder */
        $builder = $this->getMockHelper()->mockObject(
            GenericObjectBuilder::class,
            [
                'constructor' => true,
                'constructorArgs' => [$objectPrototype, $buildersContainer],
                'methods' => [
                    'createPropertiesSetter' => $propertiesSetter,
                ],
            ]
        );
        // Due to the fact that the rawData array contains only one item
        // and the method "set" of PropertiesSetter mocked to always throw an exception,
        // the handler call is expected only once.
        $rawData = ['foo' => 'bar'];
        $this->expectExceptionObject($exception);
        $builder->build($rawData);
    }

    /**
     * @return array
     *
     * @throws \SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException
     */
    public function dataForPropertiesExceptionHandlingTesting()
    {
        $exception = new PropertiesSetterException();

        $propertiesSetter = $this->getMockHelper()->mockObject(
            PropertiesSetterInterface::class,
            [
                'mockType' => MockHelper::MOCK_TYPE_ABSTRACT,
                'methods' => ['set'],
            ]
        );
        $propertiesSetter->method('set')->willReturnCallback(
            function () use ($exception) {
                throw $exception;
            }
        );

        $objectPrototype = new \stdClass();
        /** @var BuildersContainerInterface $buildersContainer */
        $buildersContainer = $this->getMockHelper()->mockObject(
            BuildersContainerInterface::class,
            ['mockType' => MockHelper::MOCK_TYPE_ABSTRACT]
        );

        return [
            [
                'exception' => $exception,
                'propertiesSetter' => $propertiesSetter,
                'objectPrototype' => $objectPrototype,
                'buildersContainer' => $buildersContainer,
            ],
        ];
    }

    /**
     * @covers ::createPropertiesSetter
     *
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
