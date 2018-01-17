<?php

namespace SilenceDis\ObjectBuilder\Test\Fixture;

/**
 * This exception is just not to extend {@see \Exception}.
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
class NonStandardException implements \Throwable
{
    public function getMessage()
    {
    }

    public function getCode()
    {
    }

    public function getFile()
    {
    }

    public function getLine()
    {
    }

    public function getTrace()
    {
    }

    public function getTraceAsString()
    {
    }

    public function getPrevious()
    {
    }

    public function __toString()
    {
    }
}

