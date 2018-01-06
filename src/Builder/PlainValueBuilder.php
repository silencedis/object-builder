<?php

namespace SilenceDis\ObjectBuilder\Builder;

use SilenceDis\ObjectBuilder\BuildersContainer\BuildersContainerInterface;

/**
 * Class PlainValueBuilder
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
class PlainValueBuilder implements BuilderInterface
{
    public function build($rawData, BuildersContainerInterface $objectBuildersContainer)
    {
        return $rawData;
    }
}
