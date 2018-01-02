<?php

namespace SilenceDis\ObjectBuilder\Test\Fixture;

/**
 * Class PublicPropertyWithSetterObject
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
class PublicPropertyWithSetterObject
{
    public $foo;

    public function setFoo($value)
    {
        $this->foo = $value;
    }
}
