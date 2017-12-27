<?php

namespace SilenceDis\ObjectBuilder\BuildersContainer;

use SilenceDis\ObjectBuilder\Builder\ObjectBuilderInterface;

/**
 * Interface BuildersContainerInterface
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
interface BuildersContainerInterface
{
    /**
     * @param string $id
     *
     * @return \SilenceDis\ObjectBuilder\Builder\ObjectBuilderInterface
     * @throws \SilenceDis\ObjectBuilder\BuildersContainer\BuilderNotFoundExceptionInterface
     */
    public function get(string $id): ObjectBuilderInterface;

    /**
     * @param string $id
     *
     * @return bool
     */
    public function has(string $id): bool;
}
