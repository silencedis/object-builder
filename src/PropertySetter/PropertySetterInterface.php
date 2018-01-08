<?php

namespace SilenceDis\ObjectBuilder\PropertySetter;

/**
 * Interface PropertiesSetterInterface
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
interface PropertySetterInterface
{
    /**
     * Indicates whether the property setter can set x
     *
     * @param \ReflectionClass $objectReflection
     * @param string $propertyName
     * @param mixed $value
     *
     * @return mixed
     */
    public function canSet(\ReflectionClass $objectReflection, string $propertyName, $value): bool;

    /**
     * Sets the value
     *
     * @param object $object An object, the property of which must be set
     * @param string $propertyName A property which must be set
     * @param mixed $value A value
     *
     * @throws \TypeError
     * @throws PropertySetterExceptionInterface
     */
    public function set($object, string $propertyName, $value): void;
}
