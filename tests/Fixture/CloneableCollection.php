<?php

namespace SilenceDis\ObjectBuilder\Test\Fixture;

use SilenceDis\ObjectBuilder\AuxInterface\ObjectsCollectionInterface;

/**
 * Class CloneableCollection
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
class CloneableCollection extends \ArrayObject implements ObjectsCollectionInterface
{
    public function isAllowed($value): bool
    {
        return $value instanceof ObjectsCollectionItem;
    }
}

