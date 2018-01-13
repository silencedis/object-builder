<?php

namespace SilenceDis\ObjectBuilder\Test\BuildersContainer;

use PHPUnit\Framework\TestCase;
use SilenceDis\ObjectBuilder\Builder\BuilderInterface;
use SilenceDis\ObjectBuilder\BuildersContainer\BuilderNotFoundExceptionInterface;
use SilenceDis\ObjectBuilder\BuildersContainer\BuildersContainer;
use SilenceDis\PHPUnitMockHelper\MockHelper;

class BuildersContainerTest extends TestCase
{
    /**
     * The method "get" throws exception if builder with the specified id doesn't exist.
     *
     * @covers \SilenceDis\ObjectBuilder\BuildersContainer\BuildersContainer::get
     * @covers \SilenceDis\ObjectBuilder\BuildersContainer\BuildersContainer::registerBuilder
     *
     * @throws \SilenceDis\ObjectBuilder\BuildersContainer\BuilderNotFoundException
     */
    public function testGet_1()
    {
        $container = new BuildersContainer();
        $this->expectException(BuilderNotFoundExceptionInterface::class);
        $container->get('foo');
    }

    /**
     * If builder with the specified id exists in the container, it will be returned.
     *
     * @covers \SilenceDis\ObjectBuilder\BuildersContainer\BuildersContainer::get
     * @covers \SilenceDis\ObjectBuilder\BuildersContainer\BuildersContainer::registerBuilder
     *
     * @throws \SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException
     * @throws \SilenceDis\ObjectBuilder\BuildersContainer\BuilderNotFoundException
     */
    public function testGet_2()
    {
        $container = new BuildersContainer();
        $builderId = 'foo';
        /** @var BuilderInterface $builder */
        $builder = (new MockHelper($this))->mockObject(
            BuilderInterface::class,
            ['mockType' => MockHelper::MOCK_TYPE_ABSTRACT]
        );
        $container->registerBuilder($builderId, $builder);
        $result = $container->get($builderId);
        $this->assertEquals($builder, $result);
    }

    /**
     * If builder with the specified id doesn't exist, `false` will be returned.
     *
     * @covers \SilenceDis\ObjectBuilder\BuildersContainer\BuildersContainer::has
     * @covers \SilenceDis\ObjectBuilder\BuildersContainer\BuildersContainer::registerBuilder
     */
    public function testHas_1()
    {
        $container = new BuildersContainer();
        $this->assertTrue($container->has('foo') === false);
    }

    /**
     * If builder with the specified id exists, `true` will be returned.
     *
     * @covers \SilenceDis\ObjectBuilder\BuildersContainer\BuildersContainer::has
     * @covers \SilenceDis\ObjectBuilder\BuildersContainer\BuildersContainer::registerBuilder
     *
     * @throws \SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException
     */
    public function testHas_2()
    {
        $container = new BuildersContainer();
        /** @var BuilderInterface $builder */
        $builder = (new MockHelper($this))->mockObject(
            BuilderInterface::class,
            ['mockType' => MockHelper::MOCK_TYPE_ABSTRACT]
        );
        $container->registerBuilder('foo', $builder);
        $this->assertTrue($container->has('foo') === true);
    }
}
