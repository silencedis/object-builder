<?php

namespace SilenceDis\ObjectBuilder\Test\Fixture;

/**
 * Class TypeHintedButNotRequiredPropertyObject
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
class TypeHintedButNotRequiredPropertyObject
{
    public function setFoo(\stdClass $value = null)
    {
    }
}
