<?php

declare(strict_types=1);

use Illuminate\Process\Factory;
use Kauffinger\Pyman\Exceptions\PymanException;
use Kauffinger\Pyman\PythonEnvironmentManager;

beforeEach(function (): void {
    $this->factory = new Factory;
    $this->basePath = sys_get_temp_dir().'/pyman_test_'.uniqid();
    $this->manager = new PythonEnvironmentManager($this->basePath, $this->factory);
});

afterEach(function (): void {
    if (is_dir($this->basePath)) {
        exec("rm -rf {$this->basePath}");
    }
});

test('constructor sets python directory and process factory', function (): void {
    expect($this->manager)->toBeInstanceOf(PythonEnvironmentManager::class);
});

test('setup runs all required steps successfully', function (): void {
    // Create requirements.txt with a simple package
    mkdir($this->basePath, 0755, true);
    file_put_contents($this->basePath.'/requirements.txt', 'requests==2.31.0');

    $this->manager->setup();

    // Verify the virtual environment was created
    expect(is_dir($this->basePath.'/venv'))->toBeTrue()
        ->and(is_file($this->basePath.'/venv/bin/pip'))->toBeTrue()
        ->and(is_file($this->basePath.'/venv/bin/python3'))->toBeTrue();

    // Verify the package was installed
    $result = $this->factory->path($this->basePath)
        ->run([$this->basePath.'/venv/bin/pip', 'freeze']);

    expect($result->output())->toContain('requests==2.31.0');
});

test('setup throws exception when python3 is not installed', function (): void {
    $this->factory->fake([
        'command -v python3' => $this->factory->result('', '', 1),
    ]);

    try {
        $this->manager->setup();
        test()->fail('Expected PymanException was not thrown');
    } catch (PymanException $e) {
        expect($e->getMessage())->toBe('python3 is required but not installed.');
    }
});

test('setup throws exception when pip3 is not installed', function (): void {
    $this->factory->fake([
        'command -v python3' => $this->factory->result(''),
        'command -v pip3' => $this->factory->result('', '', 1),
    ]);

    try {
        $this->manager->setup();
        test()->fail('Expected PymanException was not thrown');
    } catch (PymanException $e) {
        expect($e->getMessage())->toBe('pip3 is required but not installed.');
    }
});

test('createPythonDirectory throws exception when directory creation fails', function (): void {
    // Create a file with the same name as our directory to force mkdir to fail
    file_put_contents($this->basePath, '');

    try {
        $this->manager->setup();
        test()->fail('Expected PymanException was not thrown');
    } catch (PymanException $e) {
        expect($e->getMessage())->toBe('Failed to create directory: '.$this->basePath);
    }
});

test('installDependencies throws exception when requirements.txt is missing', function (): void {
    mkdir($this->basePath, 0755, true);

    try {
        $this->manager->setup();
        test()->fail('Expected PymanException was not thrown');
    } catch (PymanException $e) {
        expect($e->getMessage())->toBe('requirements.txt not found in '.$this->basePath);
    }
});

test('installDependencies throws exception when pip install fails', function (): void {
    // Create requirements.txt with an invalid package
    mkdir($this->basePath, 0755, true);
    file_put_contents($this->basePath.'/requirements.txt', 'this-package-definitely-does-not-exist==1.0.0');

    try {
        $this->manager->setup();
        test()->fail('Expected PymanException was not thrown');
    } catch (PymanException $e) {
        expect($e->getMessage())->toContain('Failed to install dependencies');
    }
});
