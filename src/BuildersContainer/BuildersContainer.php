<?php

namespace SilenceDis\ObjectBuilder\BuildersContainer;

use SilenceDis\ObjectBuilder\Builder\ObjectBuilderInterface;

/**
 * Class BuildersContainer
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
class BuildersContainer implements BuildersContainerInterface
{
    /**
     * @var ObjectBuilderInterface[]
     */
    private $builders = [];

    /**
     * @param string $id
     * @param ObjectBuilderInterface $builder
     */
    public function registerBuilder(string $id, ObjectBuilderInterface $builder): void
    {
        $this->builders[$id] = $builder;
    }

    /**
     * @inheritDoc
     */
    public function get(string $id): ObjectBuilderInterface
    {
        if (!isset($this->builders[$id])) {
            throw new BuilderNotFoundException();
        }

        return $this->builders[$id];
    }

    /**
     * @inheritDoc
     */
    public function has(string $id): bool
    {
        return isset($this->builders[$id]);
    }
}
