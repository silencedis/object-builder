<?php

namespace SilenceDis\ObjectBuilder\Test\Fixture;

/**
 * Class PrivatePropertiesObject
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
class PrivatePropertiesObject
{
    /**
     * @var mixed
     */
    private $foo;
    /**
     * @var string
     */
    private $bar;

    /**
     * @return mixed
     */
    public function getFoo()
    {
        return $this->foo;
    }

    /**
     * @param mixed $foo
     */
    public function setFoo($foo): void
    {
        $this->foo = $foo;
    }

    /**
     * @return string
     */
    public function getBar(): string
    {
        return $this->bar;
    }

    /**
     * @param string $bar
     */
    public function setBar(string $bar): void
    {
        $this->bar = $bar;
    }
}
