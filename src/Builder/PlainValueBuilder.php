<?php

namespace SilenceDis\ObjectBuilder\Builder;

/**
 * Class PlainValueBuilder
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
class PlainValueBuilder implements BuilderInterface
{
    public function build($rawData)
    {
        return $rawData;
    }
}
