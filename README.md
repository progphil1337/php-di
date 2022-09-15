# PHP-HTML
Simple but effective dependency injection library

## Installation

Install with composer:
```bash
$ composer require progphil1337/php-di
```

## Compatibility

`ProgPhil1337\DependencyInjection` requires PHP 8.1 (or better).

## Usage


### Basic example
ExampleForm.php
```php
use ProgPhil1337\DependencyInjection\ClassLookup;
use ProgPhil1337\DependencyInjection\Injector;

use MyApp\YamlConfig;
use MyApp\AbstractConfig;
use MyApp\SingletonInterface;

$lookup = new ClassLookup();
$injector = new Injector($lookup);

$lookup
    // class aliases
    ->alias(YamlConfig::class, AbstractConfig::class)

    // register singletons
    ->singleton(SingletonInterface::class)

    // Register classes that cannot be created
    ->register(new YamlConfig()) 
;

// Example for alias and registering
$yamlConfig = $injector->create(AbstractConfig::class);
```