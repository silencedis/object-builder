<?php

namespace SilenceDis\ObjectBuilder\AuxInterface;

/**
 * Interface ObjectsCollectionInterface
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
interface ObjectsCollectionInterface extends \ArrayAccess
{
    public function isAllowed($value): bool;
}
