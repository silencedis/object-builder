<?php

namespace SilenceDis\ObjectBuilder\PropertySetter;

/**
 * Class SetPropertyDirectly
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
class SetPropertyDirectly implements PropertySetterInterface
{
    /**
     * @inheritDoc
     */
    public function set($object, string $propertyName, $value): void
    {
        if (!is_object($object)) {
            throw new \TypeError(
                sprintf(
                    "Argument \"object\" passed to the constructor of %s must be an object. \"%s\" given.",
                    __CLASS__,
                    gettype($object)
                )
            );
        }

        $objectReflection = new \ReflectionClass($object);
        if (!$objectReflection->hasProperty($propertyName)) {
            throw new PropertySetterException(
                sprintf('The property "%s" doesn\'t exist in %s', $propertyName, get_class($object))
            );
        }
        $propertyReflection = $objectReflection->getProperty($propertyName);
        if (!$propertyReflection->isPublic()) {
            throw new PropertySetterException(
                sprintf('The property %s must be accessible to set it directly.', $propertyReflection->getName())
            );
        }

        $propertyReflection->setValue($object, $value);
    }

    public function canSet(\ReflectionClass $objectReflection, string $propertyName, $value): bool
    {
        if (!$objectReflection->hasProperty($propertyName)) {
            return false;
        }

        $propertyReflection = $objectReflection->getProperty($propertyName);
        if (!$propertyReflection->isPublic()) {
            return false;
        }

        return true;
    }
}
