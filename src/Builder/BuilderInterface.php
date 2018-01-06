<?php

namespace SilenceDis\ObjectBuilder\Builder;

use SilenceDis\ObjectBuilder\BuildersContainer\BuildersContainerInterface;

/**
 * Interface BuilderInterface
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
interface BuilderInterface
{
    /**
     * @param mixed $rawData
     *
     * @param \SilenceDis\ObjectBuilder\BuildersContainer\BuildersContainerInterface $objectBuildersContainer
     *
     * @return mixed
     */
    public function build($rawData, BuildersContainerInterface $objectBuildersContainer);
}
