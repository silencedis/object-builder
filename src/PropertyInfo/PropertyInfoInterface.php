<?php

namespace SilenceDis\ObjectBuilder\PropertyInfo;

/**
 * Interface PropertyInfoInterface
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
interface PropertyInfoInterface
{
    /**
     * @return bool
     */
    public function hasAccessibleField(): bool;

    /**
     * @return bool
     */
    public function hasAccessibleSetter(): bool;
}
