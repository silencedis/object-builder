<?php

namespace SilenceDis\ObjectBuilder\PropertyInfo;

/**
 * Class PropertyInfo
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
class PropertyInfo implements PropertyInfoInterface
{
    private $object;
    private $objectReflection;
    private $propertyName;

    public function __construct($object, string $propertyName)
    {
        $this->object = $object;
        $this->objectReflection = new \ReflectionClass($object);
        $this->propertyName = $propertyName;
    }

    public function hasAccessibleField(): bool
    {
        if (!$this->objectReflection->hasProperty($this->propertyName)) {
            return false;
        }
        $fieldReflection = $this->objectReflection->getProperty($this->propertyName);

        return $fieldReflection->isPublic();
    }

    public function hasAccessibleSetter(): bool
    {
    }

    public function getObject()
    {
    }

    public function getObjectReflection(): \ReflectionClass
    {
    }
}

