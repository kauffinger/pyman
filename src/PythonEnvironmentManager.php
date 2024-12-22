<?php

declare(strict_types=1);

namespace Kauffinger\Pyman;

use Illuminate\Process\Factory;
use Illuminate\Process\PendingProcess;
use Kauffinger\Pyman\Exceptions\PymanException;

class PythonEnvironmentManager
{
    private readonly string $pythonDir;

    public function __construct(string $basePath, private readonly Factory $processFactory)
    {
        $this->pythonDir = realpath($basePath) ?: $basePath;
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
        $this->installDependencies();
    }

    /**
     * @throws PymanException
     */
    private function checkPythonRequirements(): void
    {
        $commands = ['python3', 'pip3'];

        foreach ($commands as $command) {
            $result = $this->makeProcess()->run("command -v $command");
            if (! $result->successful()) {
                throw new PymanException("$command is required but not installed.");
            }
        }
    }

    /**
     * @throws PymanException
     */
    private function createPythonDirectory(): void
    {
        if (! is_dir($this->pythonDir) && ! mkdir($this->pythonDir, 0755, true)) {
            throw new PymanException("Failed to create directory: {$this->pythonDir}");
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
                throw new PymanException('Failed to create virtual environment: '.$result->errorOutput());
            }
        }
    }

    /**
     * @throws PymanException
     */
    private function installDependencies(): void
    {
        $requirementsPath = $this->pythonDir.'/requirements.txt';

        if (! file_exists($requirementsPath)) {
            throw new PymanException("requirements.txt not found in {$this->pythonDir}");
        }

        $venvPip = $this->pythonDir.'/venv/bin/pip';

        $result = $this->makeProcess()->path($this->pythonDir)
            ->timeout(300)
            ->run([$venvPip, 'install', '-r', 'requirements.txt', '-q']);

        if (! $result->successful()) {
            throw new PymanException('Failed to install dependencies: '.$result->errorOutput());
        }
    }

    private function makeProcess(): PendingProcess
    {
        return $this->processFactory->newPendingProcess();
    }
}
