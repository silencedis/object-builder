<?php

namespace SilenceDis\ObjectBuilder\Test\Builder;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SilenceDis\ObjectBuilder\AuxInterface\ObjectsCollectionInterface;
use SilenceDis\ObjectBuilder\Builder\BuilderExceptionInterface;
use SilenceDis\ObjectBuilder\Builder\BuilderInterface;
use SilenceDis\ObjectBuilder\Builder\ObjectsCollectionBuilder;
use SilenceDis\ObjectBuilder\BuildersContainer\BuildersContainerInterface;
use SilenceDis\ObjectBuilder\Test\Fixture\CloneableCollection;
use SilenceDis\ObjectBuilder\Test\Fixture\NotCloneableCollection;
use SilenceDis\ObjectBuilder\Test\Fixture\ObjectsCollectionItem;
use SilenceDis\PHPUnitMockHelper\MockHelper;

/**
 * Class ObjectsCollectionBuilderTest
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 *
 * @coversDefaultClass \SilenceDis\ObjectBuilder\Builder\ObjectsCollectionBuilder
 */
class ObjectsCollectionBuilderTest extends TestCase
{
    /**
     * If the collection prototype isn't cloneable, the InvalidArgumentException will be thrown.
     *
     * @covers ::__construct
     *
     * @throws \SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException
     */
    public function testConstructor_1()
    {
        $collectionPrototype = new NotCloneableCollection();
        $collectionItemType = \stdClass::class;
        /** @var BuildersContainerInterface $buildersContainer */
        $buildersContainer = (new MockHelper($this))->mockObject(
            BuildersContainerInterface::class,
            ['mockType' => MockHelper::MOCK_TYPE_ABSTRACT]
        );
        $this->expectException(\InvalidArgumentException::class);
        new ObjectsCollectionBuilder($collectionPrototype, $collectionItemType, $buildersContainer);
    }

    /**
     * If the rawData parameter isn't iterable, the TypeError will be thrown.
     *
     * @covers ::build
     * @covers ::__construct
     *
     * @dataProvider dataNotIterableValues
     *
     * @param $notIterableRawValue
     *
     * @throws \SilenceDis\ObjectBuilder\Builder\BuilderException
     * @throws \SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException
     * @throws \TypeError
     */
    public function testBuild_1($notIterableRawValue)
    {
        /** @var ObjectsCollectionInterface $collectionPrototype */
        $collectionPrototype = (new MockHelper($this))->mockObject(
            ObjectsCollectionInterface::class,
            ['mockType' => MockHelper::MOCK_TYPE_ABSTRACT]
        );
        $collectionItemType = ObjectsCollectionItem::class;
        /** @var BuildersContainerInterface $buildersContainer */
        $buildersContainer = (new MockHelper($this))->mockObject(
            BuildersContainerInterface::class,
            ['mockType' => MockHelper::MOCK_TYPE_ABSTRACT]
        );
        $builder = new ObjectsCollectionBuilder($collectionPrototype, $collectionItemType, $buildersContainer);
        $this->expectException(\TypeError::class);
        $builder->build($notIterableRawValue);
    }

    /**
     * @return array
     */
    public function dataNotIterableValues()
    {
        return [
            ['string'],
            [1],
            [1.1],
            [null],
            [true],
            [false],
            [new \stdClass()],
            [
                function () {
                },
            ],
        ];
    }

    /**
     * If the builders container doesn't contain a builder for the collection item type,
     * the BuildersContainerExceptionInterface exception will be thrown.
     *
     * @covers ::build
     * @covers ::__construct
     *
     * @throws \SilenceDis\ObjectBuilder\Builder\BuilderException
     * @throws \SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException
     * @throws \TypeError
     */
    public function testBuild_2()
    {
        /** @var ObjectsCollectionInterface $collectionPrototype */
        $collectionPrototype = (new MockHelper($this))->mockObject(
            ObjectsCollectionInterface::class,
            ['mockType' => MockHelper::MOCK_TYPE_ABSTRACT]
        );
        $collectionItemType = ObjectsCollectionItem::class; // The type doesn't matter.
        /** @var BuildersContainerInterface $buildersContainer */
        $buildersContainer = (new MockHelper($this))->mockObject(
            BuildersContainerInterface::class,
            [
                'mockType' => MockHelper::MOCK_TYPE_ABSTRACT,
                'methods' => [
                    // The "has" method returns `false` to provoke throwing BuilderExceptionInterface exception
                    'has' => false,
                ],
            ]
        );
        $rawValue = []; // The content of the array doesn't matter. The value just must be iterable.

        $builder = new ObjectsCollectionBuilder($collectionPrototype, $collectionItemType, $buildersContainer);
        $this->expectException(BuilderExceptionInterface::class);
        $builder->build($rawValue);
    }

    /**
     * If the builders container have the appropriate builder
     * and the collection item type is allowed by the objects collection,
     * the collection wil be built successfully.
     *
     * @covers ::build
     * @covers ::__construct
     *
     * @throws \SilenceDis\ObjectBuilder\Builder\BuilderException
     * @throws \SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException
     * @throws \TypeError
     */
    public function testBuild_3()
    {
        $mockHelper = new MockHelper($this);
        $collectionPrototype = new CloneableCollection();
        /** @var MockObject|BuildersContainerInterface $buildersContainer */
        $buildersContainer = $mockHelper->mockObject(
            BuildersContainerInterface::class,
            [
                'mockType' => MockHelper::MOCK_TYPE_ABSTRACT,
                'methods' => [
                    'has' => true,
                    'get' => $mockHelper->mockObject(
                        BuilderInterface::class,
                        [
                            'mockType' => MockHelper::MOCK_TYPE_ABSTRACT,
                            'methods' => [
                                'build' => new ObjectsCollectionItem(),
                            ],
                        ]
                    ),
                ],
            ]
        );
        $collectionItemType = ObjectsCollectionItem::class;
        $builder = new ObjectsCollectionBuilder($collectionPrototype, $collectionItemType, $buildersContainer);
        // The content of the array doesn't matter. The value just must be iterable to prevent an exception.
        // The building operations are mocked.
        // The only thing important in this array is the number of items.
        // Because the result objects collection will contain the appropriate number of objects.
        $rawData = [[], [], []];
        $expectedResult = new CloneableCollection(
            [
                new ObjectsCollectionItem(),
                new ObjectsCollectionItem(),
                new ObjectsCollectionItem(),
            ]
        );
        $result = $builder->build($rawData);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * If the builders container have the appropriate builder
     * and the collection item type is allowed by the objects collection,
     * the collection wil be built successfully.
     *
     * @covers ::build
     * @covers ::__construct
     *
     * @throws \SilenceDis\ObjectBuilder\Builder\BuilderException
     * @throws \SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException
     * @throws \TypeError
     */
    public function testBuild_4()
    {
        $mockHelper = new MockHelper($this);
        $collectionPrototype = new CloneableCollection();
        /** @var MockObject|BuildersContainerInterface $buildersContainer */
        $buildersContainer = $mockHelper->mockObject(
            BuildersContainerInterface::class,
            [
                'mockType' => MockHelper::MOCK_TYPE_ABSTRACT,
                'methods' => [
                    'has' => true,
                    'get' => $mockHelper->mockObject(
                        BuilderInterface::class,
                        [
                            'mockType' => MockHelper::MOCK_TYPE_ABSTRACT,
                            'methods' => [
                                'build' => new \stdClass(), // An instance that isn't allowed by objects collection
                            ],
                        ]
                    ),
                ],
            ]
        );
        $collectionItemType = ObjectsCollectionItem::class;
        $builder = new ObjectsCollectionBuilder($collectionPrototype, $collectionItemType, $buildersContainer);
        // The content of the array doesn't matter. The value just must be iterable to prevent an exception.
        // The building operations are mocked.
        // The only thing important in this array is the number of items.
        // Because the result objects collection will contain the appropriate number of objects.
        $rawData = [[], [], []];
        $this->expectException(BuilderExceptionInterface::class);
        $builder->build($rawData);
    }
}
