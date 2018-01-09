<?php

namespace SilenceDis\ObjectBuilder\Builder;

/**
 * Class DateTimeBuilder
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
class DateTimeBuilder implements BuilderInterface
{
    public function build($rawData): \DateTime
    {
        return new \DateTime($rawData);
    }
}
