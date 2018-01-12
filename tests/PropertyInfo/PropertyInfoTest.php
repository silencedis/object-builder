<?php

namespace SilenceDis\ObjectBuilder\Test\PropertyInfo;

use PHPUnit\Framework\TestCase;
use SilenceDis\ObjectBuilder\PropertyInfo\PropertyInfo;
use SilenceDis\ObjectBuilder\Test\Fixture\NoPropertiesObject;
use SilenceDis\ObjectBuilder\Test\Fixture\PrivatePropertiesObject;
use SilenceDis\ObjectBuilder\Test\Fixture\PublicPropertiesObject;

class PropertyInfoTest extends TestCase
{
    /**
     * @covers       \SilenceDis\ObjectBuilder\PropertyInfo\PropertyInfo::publicPropertyExists
     * @covers       \SilenceDis\ObjectBuilder\PropertyInfo\PropertyInfo::__construct
     *
     * @dataProvider dataPublicPropertyExists
     *
     * @param object $object
     * @param string $propertyName
     * @param bool $expectedResult
     */
    public function testPublicPropertyExists($object, $propertyName, $expectedResult)
    {
        $info = new PropertyInfo($object, $propertyName);
        $actualResult = $info->publicPropertyExists();
        $this->assertTrue($expectedResult === $actualResult);
    }

    /**
     * @return array
     */
    public function dataPublicPropertyExists()
    {
        return [
            [
                new PublicPropertiesObject(),
                'foo',
                true,
            ],
            [
                new PrivatePropertiesObject(),
                'foo',
                false,
            ],
            [
                new NoPropertiesObject(),
                'foo', // This property doesn't exist in the object
                false,
            ],
        ];
    }

    /**
     * @covers       \SilenceDis\ObjectBuilder\PropertyInfo\PropertyInfo::publicSetterExists()
     * @covers       \SilenceDis\ObjectBuilder\PropertyInfo\PropertyInfo::__construct
     *
     * @dataProvider dataPublicSetterExists
     *
     * @param $object
     * @param $propertyName
     * @param $expectedResult
     */
    public function testPublicSetterExists($object, $propertyName, $expectedResult)
    {
        $info = new PropertyInfo($object, $propertyName);
        $actualResult = $info->publicSetterExists();
        $this->assertTrue($expectedResult === $actualResult);
    }

    /**
     * @return array
     */
    public function dataPublicSetterExists()
    {
        return [
            [
                new PrivatePropertiesObject(),
                'foo',
                true,
            ],
            [
                new PublicPropertiesObject(),
                'foo',
                false,
            ],
            [
                new NoPropertiesObject(),
                'foo',
                false,
            ],
        ];
    }

    /**
     * @covers \SilenceDis\ObjectBuilder\PropertyInfo\PropertyInfo::getObject
     * @covers \SilenceDis\ObjectBuilder\PropertyInfo\PropertyInfo::__construct
     */
    public function testGetObject()
    {
        $object = new PublicPropertiesObject();
        $propertyName = 'asdf'; // The property name doesn't matter in this test
        $propertyInfo = new PropertyInfo($object, $propertyName);
        $actualResult = $propertyInfo->getObject();
        $this->assertTrue($actualResult === $object);
    }

    /**
     * @covers \SilenceDis\ObjectBuilder\PropertyInfo\PropertyInfo::getObjectReflection
     * @covers \SilenceDis\ObjectBuilder\PropertyInfo\PropertyInfo::__construct
     */
    public function testGetObjectReflection()
    {
        $object = new PublicPropertiesObject();
        $objectReflection = new \ReflectionClass($object);
        $propertyName = 'asdf'; // The property name doesn't matter in this test
        $propertyInfo = new PropertyInfo($object, $propertyName);
        $actualResult = $propertyInfo->getObjectReflection();
        $this->assertEquals($objectReflection, $actualResult);
    }
}
