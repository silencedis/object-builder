<?php

namespace SilenceDis\ObjectBuilder\Test\Fixture;

use SilenceDis\ObjectBuilder\AuxInterface\ObjectsCollectionInterface;

/**
 * Class NotCloneableCollection
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
class NotCloneableCollection extends \ArrayObject implements ObjectsCollectionInterface
{
    public function isAllowed($value): bool
    {
        // Tre result of this method shouldn't matter.
        // It's just for testing how the ObjectsCollectionBuilder will behave
        // with not cloneable collection prototype.
        return true;
    }

    /**
     * This method is private to make the class not cloneable
     */
    private function __clone()
    {
    }
}

