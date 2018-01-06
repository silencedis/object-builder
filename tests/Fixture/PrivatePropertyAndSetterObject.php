<?php

namespace SilenceDis\ObjectBuilder\Test\Fixture;

/**
 * Class PrivatePropertyAndSetterObject
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
class PrivatePropertyAndSetterObject
{
    private $foo;

    /**
     * This method is not private but protected
     * for an ability to mock it.
     * @param $value
     */
    protected function setFoo($value)
    {
        $this->foo = $value;
    }
}
