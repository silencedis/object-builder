<?php

namespace SilenceDis\ObjectBuilder\Builder;

use SilenceDis\ObjectBuilder\BuildersContainer\BuilderNotFoundExceptionInterface;
use SilenceDis\ObjectBuilder\BuildersContainer\BuildersContainerInterface;
use SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetterException;
use SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetter;

/**
 * Class GenericObjectBuilder
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
class GenericObjectBuilder implements BuilderInterface
{
    /**
     * @var object
     */
    private $objectPrototype;

    /**
     * GenericObjectBuilder constructor.
     *
     * @param object $objectPrototype An object. It will be used as a prototype to create a new instance.
     *                                This is to prevent changing of the original one.
     *
     * @throws \TypeError
     */
    public function __construct($objectPrototype)
    {
        if (!is_object($objectPrototype)) {
            throw new \TypeError(
                sprintf('The property "objectPrototype" must be an object, given "%s"', gettype($objectPrototype))
            );
        }

        $this->objectPrototype = $objectPrototype;
    }

    /**
     * @param mixed $rawData
     * @param BuildersContainerInterface $objectBuildersContainer
     *
     * @return mixed|object
     * @throws BuilderNotFoundExceptionInterface
     * @throws PropertiesSetterException
     */
    public function build($rawData, BuildersContainerInterface $objectBuildersContainer)
    {
        $object = clone($this->objectPrototype);
        $setter = new PropertiesSetter($object, $objectBuildersContainer);
        foreach ($rawData as $propertyName => $rawValue) {
            $setter->set($propertyName, $rawValue);
        }

        return $object;
    }
}
