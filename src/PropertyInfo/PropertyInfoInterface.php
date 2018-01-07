<?php

namespace SilenceDis\ObjectBuilder\PropertyInfo;

/**
 * Interface PropertyInfoInterface
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
interface PropertyInfoInterface
{
    public function hasAccessibleField(): bool;

    public function hasAccessibleSetter(): bool;

    public function getObject();

    public function getObjectReflection(): \ReflectionClass;
}
