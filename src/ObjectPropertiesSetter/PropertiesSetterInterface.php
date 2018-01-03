<?php

namespace SilenceDis\ObjectBuilder\ObjectPropertiesSetter;

/**
 * Interface PropertiesSetterInterface
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
interface PropertiesSetterInterface
{
    /**
     * @param string $property
     * @param mixed $value
     * @throws PropertiesSetterExceptionInterface
     */
    public function set(string $property, $value): void;
}
