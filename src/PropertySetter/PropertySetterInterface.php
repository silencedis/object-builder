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
     * @return mixed
     */
    public function canSet(\ReflectionClass $objectReflection, string $propertyName): bool;

    /**
     * @throws PropertySetterExceptionInterface
     */
    public function set(): void;
}
