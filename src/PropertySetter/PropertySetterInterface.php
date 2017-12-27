<?php

namespace SilenceDis\ObjectBuilder\PropertySetter;

/**
 * Interface PropertySetterInterface
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
interface PropertySetterInterface
{
    /**
     * @param string $property
     * @param mixed $value
     * @throws CannotSetPropertyExceptionInterface
     */
    public function set(string $property, $value): void;
}
