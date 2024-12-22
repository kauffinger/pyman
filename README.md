# Pyman - Python Environment Manager for PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kauffinger/pyman.svg?style=flat-square)](https://packagist.org/packages/kauffinger/pyman)
[![Linting](https://img.shields.io/github/actions/workflow/status/kauffinger/pyman/formats.yml?branch=main&label=linting&style=flat-square)](https://github.com/kauffinger/pyman/actions/workflows/formats.yml)
[![Tests](https://img.shields.io/github/actions/workflow/status/kauffinger/pyman/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/kauffinger/pyman/actions/workflows/tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/kauffinger/pyman.svg?style=flat-square)](https://packagist.org/packages/kauffinger/pyman)

Pyman is a PHP package that simplifies the management of Python dependencies in PHP applications. It's designed to help you seamlessly integrate Python scripts and libraries into your PHP projects by handling virtual environment setup and dependency management.

## Features

- Automatic Python virtual environment creation
- Python dependency management via `requirements.txt`
- Easy integration with Laravel's Process handling
- Robust error handling for common Python-related issues

## Requirements

- PHP 8.3+
- Python 3.x
- pip3

## Installation

You can install the package via composer:

```bash
composer require kauffinger/pyman
```

## Usage

```php
use Kauffinger\Pyman\PythonEnvironmentManager;
use Illuminate\Process\Factory;

// Initialize the manager with a base path for your Python environment
$manager = new PythonEnvironmentManager('/path/to/python/environment', new Factory());

// Set up the environment (creates venv and installs dependencies)
try {
    $manager->setup();
} catch (PymanException $e) {
    // Handle any setup errors
    echo $e->getMessage();
}
```

### Requirements File

Create a `requirements.txt` file in your specified Python environment directory:

```txt
requests==2.31.0
# Add other Python dependencies as needed
```

## Error Handling

The package throws `PymanException` in the following cases:
- Python3 or pip3 is not installed
- Failed to create the Python directory
- Missing requirements.txt file
- Failed to install Python dependencies
- Failed to create virtual environment

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Konstantin Auffinger](https://github.com/kauffinger)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
