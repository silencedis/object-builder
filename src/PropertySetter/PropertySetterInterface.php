<?php

namespace SilenceDis\ObjectBuilder\Test\PropertySetter;

/**
 * Interface PropertiesSetterInterface
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
interface PropertySetterInterface
{
    /**
     * @throws PropertySetterExceptionInterface
     */
    public function set(): void;
}
