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
    private $property1;
    /**
     * @var string
     */
    private $property2;

    /**
     * @return mixed
     */
    public function getProperty1()
    {
        return $this->property1;
    }

    /**
     * @param mixed $property1
     */
    public function setProperty1($property1): void
    {
        $this->property1 = $property1;
    }

    /**
     * @return string
     */
    public function getProperty2(): string
    {
        return $this->property2;
    }

    /**
     * @param string $property2
     */
    public function setProperty2(string $property2): void
    {
        $this->property2 = $property2;
    }

    /**
     * @return PrivatePropertiesObject
     */
    public function getProperty3(): PrivatePropertiesObject
    {
        return $this->property3;
    }

    /**
     * @param PrivatePropertiesObject $property3
     */
    public function setProperty3(PrivatePropertiesObject $property3): void
    {
        $this->property3 = $property3;
    }
    /**
     * @var PrivatePropertiesObject
     */
    private $property3;
}
