<?php

namespace SilenceDis\ObjectBuilder\Builder;

use SilenceDis\ObjectBuilder\BuildersContainer\BuildersContainerInterface;

/**
 * Class DateTimeBuilder
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
class DateTimeBuilder implements ObjectBuilderInterface
{
    public function build($rawData, BuildersContainerInterface $objectBuildersContainer): \DateTime
    {
        return new \DateTime($rawData);
    }
}
