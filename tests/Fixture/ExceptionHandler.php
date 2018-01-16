<?php

namespace SilenceDis\ObjectBuilder\Test\Fixture;

use SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetterExceptionInterface;

/**
 * Class ExceptionHandler
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
class ExceptionHandler
{
    public function __invoke(PropertiesSetterExceptionInterface $e)
    {
    }
}

