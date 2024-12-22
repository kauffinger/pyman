<?php

declare(strict_types=1);

namespace Kauffinger\Pyman\Concerns;

use Kauffinger\Pyman\DependencyManager;

trait ManagesDependencies
{
    private DependencyManager $dependencies;

    public function addDependency(string $dependency): self
    {
        $this->dependencies->add($dependency);

        return $this;
    }

    public function clearDependencies(): self
    {
        $this->dependencies->clear();

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getDependencies(): array
    {
        return $this->dependencies->get();
    }
}
