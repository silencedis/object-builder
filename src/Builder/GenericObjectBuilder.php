<?php

namespace SilenceDis\ObjectBuilder\Builder;

use SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetterException;
use SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetterInterface;

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
     * @var PropertiesSetterInterface
     */
    private $propertiesSetter;

    /**
     * GenericObjectBuilder constructor.
     *
     * @param object $objectPrototype An object. It will be used as a prototype to create a new instance.
     *                                This is to prevent changing of the original one.
     * @param PropertiesSetterInterface $propertiesSetter
     * @throws \TypeError
     */
    public function __construct($objectPrototype, PropertiesSetterInterface $propertiesSetter)
    {
        if (!is_object($objectPrototype)) {
            throw new \TypeError(
                sprintf('The property "objectPrototype" must be an object, given "%s"', gettype($objectPrototype))
            );
        }

        $this->objectPrototype = $objectPrototype;
        $this->propertiesSetter = $propertiesSetter;
    }

    /**
     * @param mixed $rawData
     * @return mixed|object
     *
     * @throws PropertiesSetterException
     * @throws \SilenceDis\ObjectBuilder\PropertySetter\PropertySetterExceptionInterface
     * @throws \TypeError
     * @throws \SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetterExceptionInterface
     */
    public function build($rawData)
    {
        if (!is_iterable($rawData)) {
            throw new \TypeError(
                sprintf(
                    'The parameter "rawData" of the method %s must be iterable, given %s.',
                    __METHOD__,
                    gettype($rawData)
                )
            );
        }

        $object = clone($this->objectPrototype);
        foreach ($rawData as $propertyName => $rawValue) {
            $this->propertiesSetter->set($propertyName, $rawValue);
        }

        return $object;
    }
}
