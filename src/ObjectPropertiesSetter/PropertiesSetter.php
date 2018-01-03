<?php

namespace SilenceDis\ObjectBuilder\ObjectPropertiesSetter;

use SilenceDis\ObjectBuilder\BuildersContainer\BuildersContainerInterface;
use SilenceDis\ObjectBuilder\Test\PropertySetter\DirectPropertySetter;

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
     * @throws \SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetterException
     * @throws \SilenceDis\ObjectBuilder\BuildersContainer\BuilderNotFoundExceptionInterface
     */
    public function set(string $property, $value): void
    {
        if ($this->objectReflection->hasProperty($property)) {
            $propertyReflection = $this->objectReflection->getProperty($property);
            if ($propertyReflection->isPublic()) {
                $propertyReflection->setValue($this->object, $value);
                $propertySetter = new DirectPropertySetter($this->object, $property, $value);
                $propertySetter->set();

                return;
            }
        }

        $methodName = 'set'.ucfirst($property);
        if ($this->objectReflection->hasMethod($methodName)) {
            $methodReflection = $this->objectReflection->getMethod($methodName);
            if ($methodReflection->isPublic()) {
                // It's assumed that setters have only one parameter
                $parametersReflections = $methodReflection->getParameters();
                if (count($parametersReflections) !== 1) {
                    throw new PropertiesSetterException('Setters must have one parameter.');
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
            }
        }

        throw new PropertiesSetterException(
            sprintf('Cannot set property "%s" of the class "%s"', $property, $this->objectReflection->getName())
        );
    }
}
