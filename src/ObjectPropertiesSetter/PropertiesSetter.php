<?php

namespace SilenceDis\ObjectBuilder\ObjectPropertiesSetter;

use SilenceDis\ObjectBuilder\BuildersContainer\BuildersContainerInterface;
use SilenceDis\ObjectBuilder\PropertySetter\DirectPropertySetter;

/**
 * Class PropertiesSetter
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
class PropertiesSetter implements PropertiesSetterInterface
{
    /**
     * @var object
     */
    private $object;
    /**
     * @var \ReflectionClass
     */
    private $objectReflection;

    /**
     * @var BuildersContainerInterface
     */
    private $objectBuildersContainer = [];

    /**
     * PropertiesSetter constructor.
     *
     * @param object $object
     *
     * @param BuildersContainerInterface|null $objectBuildersContainer
     *
     * @throws \TypeError
     */
    public function __construct($object, BuildersContainerInterface $objectBuildersContainer = null)
    {
        if (!is_object($object)) {
            throw new \TypeError(
                sprintf(
                    "Argument \"object\" passed to %s must be an object. \"%s\" given.",
                    __METHOD__,
                    gettype($object)
                )
            );
        }
        $this->object = $object;
        $this->objectReflection = new \ReflectionClass($object);

        $this->objectBuildersContainer = $objectBuildersContainer;
    }

    /**
     * @param string $property
     * @param mixed $value
     *
     * @throws PropertiesSetterException
     * @throws \SilenceDis\ObjectBuilder\BuildersContainer\BuilderNotFoundExceptionInterface
     */
    public function set(string $property, $value): void
    {
        if ($this->objectReflection->hasProperty($property)) {
            $propertyReflection = $this->objectReflection->getProperty($property);
            if ($propertyReflection->isPublic()) {
                $propertySetter = new DirectPropertySetter($this->object, $property, $value);
                $propertySetter->set();

                return;
            }
        }

        $setterName = 'set'.ucfirst($property);
        if ($this->objectReflection->hasMethod($setterName)) {
            $this->setPropertyThroughSetter($property, $setterName, $value);

            return;
        }

        $this->throwCannotBuildPropertyException($property);
    }

    /**
     * @param string $property
     * @param $value
     * @throws PropertiesSetterException
     */
    private function setPropertyDirectly(string $property, $value): void
    {
        $propertyReflection = $this->objectReflection->getProperty($property);
        if (!$propertyReflection->isPublic()) {
            $this->throwCannotBuildPropertyException($property);
        }
        $propertyReflection->setValue($this->object, $value);
    }

    /**
     * @param string $property
     * @param string $setterName
     * @param $value
     * @throws PropertiesSetterException
     * @throws \SilenceDis\ObjectBuilder\BuildersContainer\BuilderNotFoundExceptionInterface
     */
    private function setPropertyThroughSetter(string $property, string $setterName, $value)
    {
        $methodReflection = $this->objectReflection->getMethod($setterName);
        if (!$methodReflection->isPublic()) {
            $this->throwCannotBuildPropertyException($property);
        }

        // It's assumed that setters have only one parameter
        $parametersReflections = $methodReflection->getParameters();
        if (count($parametersReflections) !== 1) {
            $this->throwCannotBuildPropertyException($property, 'Setters must have one parameter.');
        }

        $parameterReflection = array_shift($parametersReflections);
        if (!$parameterReflection->hasType() || $value === null && $parameterReflection->allowsNull()) {
            $methodReflection->invoke($this->object, $value);

            return;
        }

        $parameterType = $parameterReflection->getType()->getName();
        $valueType = is_object($value) ? get_class($value) : gettype($value);
        if ($valueType == $parameterType) {
            $methodReflection->invoke($this->object, $value);

            return;
        }

        if ($this->objectBuildersContainer !== null && $this->objectBuildersContainer->has($parameterType)) {
            $builder = $this->objectBuildersContainer->get($parameterType);
            $methodReflection->invoke($this->object, $builder->build($value, $this->objectBuildersContainer));

            return;
        }

        $this->throwCannotBuildPropertyException($property);
    }

    /**
     * @param string $property
     * @param string|null $message
     * @throws PropertiesSetterException
     */
    private function throwCannotBuildPropertyException(string $property, $message = null)
    {
        if ($message === null) {
            $message = sprintf(
                'Cannot set property "%s" of the class "%s"',
                $property,
                $this->objectReflection->getName()
            );
        }

        throw new PropertiesSetterException($message);
    }
}
