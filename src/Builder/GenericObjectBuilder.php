<?php

namespace SilenceDis\ObjectBuilder\Builder;

use SilenceDis\ObjectBuilder\BuildersContainer\BuildersContainerInterface;
use SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetter;
use SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetterException;
use SilenceDis\ObjectBuilder\PropertySetter\SetPropertyDirectly;
use SilenceDis\ObjectBuilder\PropertySetter\SetPropertyThroughSetter;

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
     * @var BuildersContainerInterface
     */
    private $buildersContainer;

    /**
     * GenericObjectBuilder constructor.
     *
     * @param object $objectPrototype An object. It will be used as a prototype to create a new instance.
     *                                This is to prevent changing of the original one.
     * @param BuildersContainerInterface $buildersContainer A builders container
     *
     * @throws \TypeError
     */
    public function __construct($objectPrototype, BuildersContainerInterface $buildersContainer)
    {
        if (!is_object($objectPrototype)) {
            throw new \TypeError(
                sprintf('The property "objectPrototype" must be an object, given "%s"', gettype($objectPrototype))
            );
        }

        $this->objectPrototype = $objectPrototype;
        $this->buildersContainer = $buildersContainer;
    }

    /**
     * @param mixed $rawData
     * @return mixed|object
     *
     * @throws PropertiesSetterException
     * @throws \SilenceDis\ObjectBuilder\PropertySetter\PropertySetterExceptionInterface
     * @throws \TypeError
     */
    public function build($rawData)
    {
        $object = clone($this->objectPrototype);
        $propertySetters = [
            new SetPropertyDirectly(),
            new SetPropertyThroughSetter($this->buildersContainer),
        ];
        $setter = new PropertiesSetter($object, $propertySetters);
        foreach ($rawData as $propertyName => $rawValue) {
            $setter->set($propertyName, $rawValue);
        }

        return $object;
    }
}
