<?php

declare(strict_types=1);

namespace Kauffinger\Pyman;

use Illuminate\Process\Factory;
use Kauffinger\Pyman\Exceptions\VenvManagementException;

class RequirementsManager
{
    /**
     * Holds list of dependencies that are required for the installation process.
     *
     * @param  array<string>  $requirements
     */
    public function __construct(
        private readonly Factory $processFactory,
        private readonly string $pythonDir,
        private array $requirements = []
    ) {}

    public function add(string $requirement): void
    {
        $this->requirements[] = $requirement;
    }

    public function clear(): void
    {
        $this->requirements = [];
    }

    /**
     * @return string[]
     */
    public function get(): array
    {
        return $this->requirements;
    }

    /**
     * Currently only works with the requirements.txt file, but will be extended to be able to use
     * passed dependencies as well.
     *
     * @throws VenvManagementException
     */
    public function install(): void
    {
        $requirementsPath = $this->pythonDir.'/requirements.txt';

        if (! file_exists($requirementsPath)) {
            throw new VenvManagementException("requirements.txt not found in {$this->pythonDir}");
        }

        $venvPip = $this->pythonDir.'/venv/bin/pip';

        $result = $this->processFactory->newPendingProcess()
            ->path($this->pythonDir)
            ->timeout(300)
            ->run([$venvPip, 'install', '-r', 'requirements.txt', '-q']);

        if (! $result->successful()) {
            throw new VenvManagementException('Failed to install requirements: '.$result->errorOutput());
        }
    }
}
