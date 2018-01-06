<?php

namespace SilenceDis\ObjectBuilder\PropertySetter;

/**
 * Class SetPropertyDirectly
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
class SetPropertyDirectly implements PropertySetterInterface
{
    /**
     * @var object
     */
    private $object;
    /**
     * @var \ReflectionProperty
     */
    private $propertyReflection;
    /**
     * @var mixed
     */
    private $value;

    /**
     * SetPropertyDirectly constructor.
     *
     * @param $object
     * @param string $property
     * @param $value
     * @throws \TypeError
     * @throws PropertySetterException
     */
    public function __construct($object, string $property, $value)
    {
        if (!is_object($object)) {
            throw new \TypeError(
                sprintf(
                    "Argument \"object\" passed to the constructor of %s must be an object. \"%s\" given.",
                    __CLASS__,
                    gettype($object)
                )
            );
        }

        $objectReflection = new \ReflectionClass($object);
        if (!$objectReflection->hasProperty($property)) {
            throw new PropertySetterException(
                sprintf('The property "%s" doesn\'t exist in %s', $property, get_class($object))
            );
        }
        $propertyReflection = $objectReflection->getProperty($property);
        if (!$propertyReflection->isPublic()) {
            throw new PropertySetterException(
                sprintf('The property %s must be accessible to set it directly.', $propertyReflection->getName())
            );
        }

        $this->object = $object;
        $this->propertyReflection = $propertyReflection;
        $this->value = $value;
    }

    /**
     * @inheritDoc
     */
    public function set(): void
    {
        $this->propertyReflection->setValue($this->object, $this->value);
    }
}
