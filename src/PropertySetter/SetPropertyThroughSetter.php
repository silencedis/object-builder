<?php

namespace SilenceDis\ObjectBuilder\PropertySetter;

use SilenceDis\ObjectBuilder\BuildersContainer\BuildersContainerInterface;

/**
 * Class SetPropertyThroughSetter
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
class SetPropertyThroughSetter implements PropertySetterInterface
{
    /**
     * @var BuildersContainerInterface
     */
    private $buildersContainer;

    /**
     * SetPropertyThroughSetter constructor.
     *
     * @param BuildersContainerInterface $buildersContainer
     */
    public function __construct(BuildersContainerInterface $buildersContainer)
    {
        $this->buildersContainer = $buildersContainer;
    }

    /**
     * @inheritDoc
     */
    public function set($object, string $propertyName, $value): void
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
        $setterMethodName = $this->getSetterMethodName($propertyName);
        if (!$objectReflection->hasMethod($setterMethodName)) {
            throw new PropertySetterException(
                sprintf('The method "%s" doesn\'t exist in %s', $setterMethodName, get_class($object))
            );
        }
        $methodReflection = $objectReflection->getMethod($setterMethodName);
        if (!$methodReflection->isPublic()) {
            throw new PropertySetterException(
                sprintf('The method %s must be accessible to set the property using it.', $methodReflection->getName())
            );
        }

        // It's assumed that setters have only one parameter
        $parametersReflections = $methodReflection->getParameters();
        if (count($parametersReflections) !== 1) {
            throw new PropertySetterException('Setters must have one parameter.');
        }

        $parameterReflection = array_shift($parametersReflections);
        if (!$parameterReflection->hasType() || $value === null && $parameterReflection->allowsNull()) {
            $methodReflection->invoke($object, $value);

            return;
        }

        $parameterType = $parameterReflection->getType()->getName();
        $valueType = is_object($value) ? get_class($value) : gettype($value);
        if ($valueType == $parameterType) {
            $methodReflection->invoke($object, $value);

            return;
        }

        if ($this->buildersContainer !== null && $this->buildersContainer->has($parameterType)) {
            $builder = $this->buildersContainer->get($parameterType);
            $methodReflection->invoke($object, $builder->build($value, $this->buildersContainer));

            return;
        }

        throw new PropertySetterException(
            sprintf(
                'Cannot set property "%s" of the class "%s"',
                $propertyName,
                $objectReflection->getName()
            )
        );
    }

    public function canSet(\ReflectionClass $objectReflection, string $propertyName, $value): bool
    {
        $setterMethodName = $this->getSetterMethodName($propertyName);

        if (!$objectReflection->hasMethod($setterMethodName)) {
            return false;
        }

        $methodReflection = $objectReflection->getMethod($setterMethodName);
        if (!$methodReflection->isPublic()) {
            return false;
        }

        // It's assumed that setters have only one parameter
        $parametersReflections = $methodReflection->getParameters();
        if (count($parametersReflections) !== 1) {
            return false;
        }

        $parameterReflection = array_shift($parametersReflections);
        if (!$parameterReflection->hasType() || $value === null && $parameterReflection->allowsNull()) {
            return true;
        }

        $parameterType = $parameterReflection->getType()->getName();
        $valueType = is_object($value) ? get_class($value) : gettype($value);
        if ($valueType == $parameterType) {
            return true;
        }

        if ($this->buildersContainer !== null && $this->buildersContainer->has($parameterType)) {
            return true;
        }

        return false;
    }

    /**
     * @param string $propertyName
     * @return string
     */
    protected function getSetterMethodName(string $propertyName)
    {
        return sprintf('set%s', ucfirst($propertyName));
    }
}
