<?php

namespace SilenceDis\ObjectBuilder\Builder;

use SilenceDis\ObjectBuilder\AuxInterface\ObjectsCollectionInterface;
use SilenceDis\ObjectBuilder\BuildersContainer\BuildersContainerInterface;

/**
 * Class ObjectsCollectionBuilder
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
class ObjectsCollectionBuilder implements BuilderInterface
{
    /**
     * @var ObjectsCollectionInterface
     */
    private $collectionPrototype;
    /**
     * @var string
     */
    private $collectionItemType;

    /**
     * ObjectsCollectionBuilder constructor.
     *
     *
     * @param ObjectsCollectionInterface $collectionPrototype
     * @param string $collectionItemType
     *
     * @throws \TypeError
     */
    public function __construct(ObjectsCollectionInterface $collectionPrototype, string $collectionItemType)
    {
        if (is_object($collectionPrototype)) {
            $collectionPrototypeReflection = new \ReflectionClass($collectionPrototype);
            if (!$collectionPrototypeReflection->isCloneable()) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'If the parameter "%s" is an object, it must be cloneable! The given type is %s.',
                        'collectionPrototype',
                        gettype($collectionPrototype)
                    )
                );
            }
        }

        $this->collectionPrototype = $collectionPrototype;
        $this->collectionItemType = $collectionItemType;
    }

    /**
     * @inheritDoc
     */
    public function build($rawData, BuildersContainerInterface $objectBuildersContainer)
    {
        if (!is_iterable($rawData)) {
            return new \TypeError(
                sprintf(
                    'The parameter "%s" of the method "%s" must be iterable, given "%s"',
                    'rawData',
                    __METHOD__,
                    gettype($rawData)
                )
            );
        }

        if (!$objectBuildersContainer->has($this->collectionItemType)) {
            throw new BuilderException(
                sprintf(
                    'Cannot create an instance of %s. The object builders container doesn\'t have suitable object builder.',
                    $this->collectionItemType
                )
            );
        }
        $collectionItemBuilder = $objectBuildersContainer->get($this->collectionItemType);
        $collection = clone $this->collectionPrototype;

        foreach ($rawData as $key => $value) {
            $collectionItem = $collectionItemBuilder->build($value, $objectBuildersContainer);
            if (!$collection->isAllowed($collectionItem)) {
                throw new BuilderException(
                    sprintf(
                        'The type "%s" is not allowed by the collection class "%s"',
                        gettype($collectionItem),
                        gettype($this->collectionPrototype)
                    )
                );
            }
        }

        return $collection;
    }
}
