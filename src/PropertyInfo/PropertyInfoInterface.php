<?php

namespace SilenceDis\ObjectBuilder\PropertyInfo;

/**
 * Interface PropertyInfoInterface
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
interface PropertyInfoInterface
{
    public function publicPropertyExists(): bool;

    public function publicSetterExists(): bool;

    public function getObject();

    public function getObjectReflection(): \ReflectionClass;
}
