<?php

namespace SilenceDis\ObjectBuilder\Builder;

use SilenceDis\ObjectBuilder\BuildersContainer\BuilderNotFoundExceptionInterface;
use SilenceDis\ObjectBuilder\BuildersContainer\BuildersContainerInterface;
use SilenceDis\ObjectBuilder\PropertySetter\CannotSetPropertyException;
use SilenceDis\ObjectBuilder\PropertySetter\PropertySetter;

/**
 * Class GenericObjectBuilder
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
class GenericObjectBuilder implements ObjectBuilderInterface
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
     * @throws CannotSetPropertyException
     */
    public function build($rawData, BuildersContainerInterface $objectBuildersContainer)
    {
        $object = clone($this->objectPrototype);
        $setter = new PropertySetter($object, $objectBuildersContainer);
        foreach ($rawData as $propertyName => $rawValue) {
            $setter->set($propertyName, $rawValue);
        }

        return $object;
    }
}
