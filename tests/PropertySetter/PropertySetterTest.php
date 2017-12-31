<?php

namespace SilenceDis\ObjectBuilder\Test\PropertySetter;

use PHPUnit\Framework\TestCase;
use SilenceDis\ObjectBuilder\BuildersContainer\BuilderNotFoundExceptionInterface;
use SilenceDis\ObjectBuilder\BuildersContainer\BuildersContainerInterface;
use SilenceDis\ObjectBuilder\PropertySetter\CannotSetPropertyException;
use SilenceDis\ObjectBuilder\PropertySetter\PropertySetter;
use SilenceDis\ObjectBuilder\Test\Fixture\PrivatePropertiesObject;
use SilenceDis\ObjectBuilder\Test\Fixture\PublicPropertiesObject;
use SilenceDis\PHPUnitMockHelper\Exception\InvalidMockTypeException;
use SilenceDis\PHPUnitMockHelper\MockHelper;

class PropertySetterTest extends TestCase
{
    /**
     * @param array $mockConfig
     * @return \PHPUnit_Framework_MockObject_MockObject|BuildersContainerInterface
     */
    private function getBuildersContainerMock($mockConfig = [])
    {
        try {
            $buildersContainer = (new MockHelper($this))->mockObject(
                BuildersContainerInterface::class,
                [
                    'mockType' => MockHelper::MOCK_TYPE_ABSTRACT,
                ],
                $mockConfig
            );

            return $buildersContainer;
        } catch (InvalidMockTypeException $e) {
            $this->markTestSkipped('Failed to mock BuildersContainerInterface');
        }
    }

    /**
     * @dataProvider dataSetNonMarkedPublicProperty
     * @param $value
     */
    public function testSetNonMarkedPublicProperty($value)
    {
        $object = new PublicPropertiesObject();
        $setter = new PropertySetter($object);

        $propertyName = 'property1';

        try {
            $setter->set($propertyName, $value);
        } catch (BuilderNotFoundExceptionInterface $e) {
        } catch (CannotSetPropertyException $e) {
            $this->fail('An exception was thrown.');
        }

        $this->assertTrue($object->{$propertyName} === $value, 'The property must be set as is.');
    }

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

    public function testSetPrivatePropertyCallsSetter()
    {
        $testPropertyName = 'property1';
        $testPropertySetter = 'setProperty1';
        $testValue = 'test string'; // The value type doesn't matter in this test

        $buildersContainer = $this->getBuildersContainerMock();

        try {
            $object = (new MockHelper($this))->mockObject(
                PrivatePropertiesObject::class,
                ['methods' => [$testPropertySetter]]
            );
        } catch (InvalidMockTypeException $e) {
            $this->markTestSkipped('Failed to create the mock of PrivatePropertiesObject.');

            return;
        }

        $setter = new PropertySetter($object, $buildersContainer);

        $object->expects($this->once())->method($testPropertySetter)->with($testValue);

        try {
            $setter->set($testPropertyName, $testValue);
        } catch (BuilderNotFoundExceptionInterface $e) {
        } catch (CannotSetPropertyException $e) {
            $this->fail('Failed to set the test property.');
        }
    }
}
