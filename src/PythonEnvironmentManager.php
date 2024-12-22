<?php

declare(strict_types=1);

namespace Kauffinger\Pyman;

use Illuminate\Process\Factory;
use Illuminate\Process\PendingProcess;
use Kauffinger\Pyman\Concerns\ManagesDependencies;
use Kauffinger\Pyman\Exceptions\FolderCouldNotBeCreatedException;
use Kauffinger\Pyman\Exceptions\PymanException;
use Kauffinger\Pyman\Exceptions\VenvManagementException;

class PythonEnvironmentManager
{
    use ManagesDependencies;

    private readonly string $pythonDir;

    private readonly RequirementsManager $requirements;

    public function __construct(string $basePath, private readonly Factory $processFactory)
    {
        $this->pythonDir = realpath($basePath) ?: $basePath;

        $this->dependencies = new DependencyManager($this->processFactory, [
            'python3',
            'pip3',
        ]);

        $this->requirements = new RequirementsManager($this->processFactory, $this->pythonDir);
    }

    /**
     * Set up the Python virtual environment and install dependencies
     *
     * @throws PymanException
     */
    public function setup(): void
    {
        $this->checkPythonRequirements();
        $this->createPythonDirectory();
        $this->createVirtualEnvironment();
        $this->installRequirements();
    }

    /**
     * @throws PymanException
     */
    private function checkPythonRequirements(): void
    {
        $this->dependencies->check();
    }

    /**
     * @throws PymanException
     */
    private function createPythonDirectory(): void
    {
        if (! is_dir($this->pythonDir) && ! mkdir($this->pythonDir, 0755, true)) {
            throw new FolderCouldNotBeCreatedException("Failed to create directory: {$this->pythonDir}");
        }
    }

    /**
     * @throws PymanException
     */
    private function createVirtualEnvironment(): void
    {
        if (! is_dir($this->pythonDir.'/venv')) {
            $result = $this->makeProcess()->path($this->pythonDir)
                ->timeout(60)
                ->run('python3 -m venv venv');

            if (! $result->successful()) {
                throw new VenvManagementException('Failed to create virtual environment: '.$result->errorOutput());
            }
        }
    }

    /**
     * @throws PymanException
     */
    private function installRequirements(): void
    {
        $this->requirements->install();
    }

    private function makeProcess(): PendingProcess
    {
        return $this->processFactory->newPendingProcess();
    }
}
