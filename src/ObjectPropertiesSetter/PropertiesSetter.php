<?php

namespace SilenceDis\ObjectBuilder\ObjectPropertiesSetter;

use SilenceDis\ObjectBuilder\PropertySetter\PropertySetterInterface;

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
     * @var iterable|PropertySetterInterface[]
     */
    private $propertySetters = [];

    /**
     * PropertiesSetter constructor.
     *
     * @param object $object
     * @param iterable|PropertySetterInterface[] $propertySetters
     *
     * @throws \TypeError
     * @throws PropertiesSetterException
     */
    public function __construct($object, iterable $propertySetters)
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

        foreach ($propertySetters as $propertySetter) {
            if (!$propertySetter instanceof PropertySetterInterface) {
                throw new PropertiesSetterException(
                    sprintf('At least one of property setters is not an instance of %s', PropertySetterInterface::class)
                );
            }

            $this->propertySetters[] = $propertySetter;
        }
    }

    /**
     * @param string $property
     * @param mixed $value
     *
     * @throws PropertiesSetterException
     * @throws \TypeError
     * @throws \SilenceDis\ObjectBuilder\PropertySetter\PropertySetterExceptionInterface
     */
    public function set(string $property, $value): void
    {
        foreach ($this->propertySetters as $propertySetter) {
            if ($propertySetter->canSet($this->objectReflection, $property, $value)) {
                try {
                    $propertySetter->set($this->object, $property, $value);
                } catch (\Exception $e) {
                    $this->throwPropertiesSetterException($property, $e);
                } catch (\TypeError $e) {
                    $this->throwPropertiesSetterException($property, $e);
                }

                return;
            }
        }

        throw new PropertiesSetterException(
            sprintf(
                'Cannot set property "%s" of the class "%s"',
                $property,
                $this->objectReflection->getName()
            )
        );
    }

    /**
     * Throws PropertiesSetterException
     *
     * @param string $propertyName
     * @param \Throwable|null $previousException
     *
     * @throws \SilenceDis\ObjectBuilder\ObjectPropertiesSetter\PropertiesSetterException
     */
    private function throwPropertiesSetterException(string $propertyName, \Throwable $previousException = null)
    {
        throw new PropertiesSetterException(
            sprintf('Cannot set property "%s" of the class "%s"', $propertyName, $this->objectReflection->getName()),
            0,
            $previousException
        );
    }
}
