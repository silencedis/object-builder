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

    public function publicPropertyExists(): bool
    {
        if (!$this->objectReflection->hasProperty($this->propertyName)) {
            return false;
        }
        $propertyReflection = $this->objectReflection->getProperty($this->propertyName);

        return $propertyReflection->isPublic();
    }

    public function publicSetterExists(): bool
    {
        $methodName = sprintf('set%s', ucfirst($this->propertyName));
        if (!$this->objectReflection->hasMethod($methodName)) {
            return false;
        }
        $methodReflection = $this->objectReflection->getMethod($methodName);

        return $methodReflection->isPublic();
    }

    public function getObject()
    {
        return $this->object;
    }

    public function getObjectReflection(): \ReflectionClass
    {
        return $this->objectReflection;
    }
}

