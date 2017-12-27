<?php

namespace SilenceDis\ObjectBuilder\BuildersContainerFactory;

use SilenceDis\ObjectBuilder\BuildersContainer\BuildersContainerInterface;

/**
 * Interface BuildersContainerFactoryInterface
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
interface BuildersContainerFactoryInterface
{
    /**
     * @return \SilenceDis\ObjectBuilder\BuildersContainer\BuildersContainerInterface
     */
    public function createContainer(): BuildersContainerInterface;
}
