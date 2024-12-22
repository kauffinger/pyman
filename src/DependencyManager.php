<?php

declare(strict_types=1);

namespace Kauffinger\Pyman;

use Illuminate\Process\Factory;
use Kauffinger\Pyman\Exceptions\MissingDependencyException;

class DependencyManager
{
    /**
     * Holds list of dependencies that are required for the installation process.
     *
     * @param  array<string>  $dependencies
     */
    public function __construct(private readonly Factory $processFactory, private array $dependencies = []) {}

    public function add(string $dependency): void
    {
        $this->dependencies[] = $dependency;
    }

    public function clear(): void
    {
        $this->dependencies = [];
    }

    /**
     * @return string[]
     */
    public function get(): array
    {
        return $this->dependencies;
    }

    public function check(): void
    {
        foreach ($this->dependencies as $command) {
            $result = $this->processFactory->newPendingProcess()
                ->run("command -v $command");

            if (! $result->successful()) {
                throw new MissingDependencyException("$command is required but not installed.");
            }
        }
    }
}
