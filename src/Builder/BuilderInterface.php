<?php

namespace SilenceDis\ObjectBuilder\Builder;

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
     * @return mixed
     */
    public function build($rawData);
}
