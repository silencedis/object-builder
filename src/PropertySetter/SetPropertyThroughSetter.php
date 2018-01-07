<?php

namespace SilenceDis\ObjectBuilder\PropertySetter;

/**
 * Class SetPropertyThroughSetter
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
class SetPropertyThroughSetter implements PropertySetterInterface
{
    /**
     * @var object
     */
    private $object;
    /**
     * @var \ReflectionMethod
     */
    private $methodReflection;
    /**
     * @var mixed
     */
    private $value;

    /**
     * SetPropertyThroughSetter constructor.
     *
     * @param object $object
     * @param string $propertyName
     * @param mixed $value
     *
     * @throws PropertySetterException
     * @throws \TypeError
     */
    public function __construct($object, string $propertyName, $value)
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
        $methodName = sprintf('set%s', ucfirst($propertyName));
        if (!$objectReflection->hasMethod($methodName)) {
            throw new PropertySetterException(
                sprintf('The method "%s" doesn\'t exist in %s', $methodName, get_class($object))
            );
        }
        $methodReflection = $objectReflection->getMethod($methodName);
        if (!$methodReflection->isPublic()) {
            throw new PropertySetterException(
                sprintf('The method %s must be accessible to set the property using it.', $methodReflection->getName())
            );
        }

        $this->object = $object;
        $this->methodReflection = $methodReflection;
        $this->value = $value;
    }

    public function set(): void
    {
        $this->methodReflection->invoke($this->object, $this->value);
    }

    public function canSet(\ReflectionClass $objectReflection, string $propertyName): bool
    {
        // TODO: Implement canSet() method.
    }
}
