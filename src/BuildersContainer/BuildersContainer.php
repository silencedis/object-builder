<?php

namespace SilenceDis\ObjectBuilder\BuildersContainer;

use SilenceDis\ObjectBuilder\Builder\BuilderInterface;

/**
 * Class BuildersContainer
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
class BuildersContainer implements BuildersContainerInterface
{
    /**
     * @var BuilderInterface[]
     */
    private $builders = [];

    /**
     * @param string $id
     * @param BuilderInterface $builder
     */
    public function registerBuilder(string $id, BuilderInterface $builder): void
    {
        $this->builders[$id] = $builder;
    }

    /**
     * @inheritDoc
     */
    public function get(string $id): BuilderInterface
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
