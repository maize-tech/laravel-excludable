<p align="center"><img src="/art/socialcard.png" alt="Social Card of Laravel Excludable"></p>

# Laravel Excludable

[![Latest Version on Packagist](https://img.shields.io/packagist/v/maize-tech/laravel-excludable.svg?style=flat-square)](https://packagist.org/packages/maize-tech/laravel-excludable)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/maize-tech/laravel-excludable/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/maize-tech/laravel-excludable/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/maize-tech/laravel-excludable/php-cs-fixer.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/maize-tech/laravel-excludable/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/maize-tech/laravel-excludable.svg?style=flat-square)](https://packagist.org/packages/maize-tech/laravel-excludable)

Easily exclude model entities from eloquent queries. 

This package allows you to define a subset of model entities that should be excluded from eloquent queries.
You will be able to override the default `Exclusion` model and its associated migration, so you can eventually restrict the exclusion context by defining the entity that should effectively exclude the subset. 

An example usage could be an application with a multi tenant scenario and a set of global entities.
While those entities should be accessible by all tenants, some of them might want to hide a subset of those entities for their users.
You can find an example in the [Usage](#usage) section.

## Installation

You can install the package via composer:

```bash
composer require maize-tech/laravel-excludable
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --provider="Maize\Excludable\ExcludableServiceProvider" --tag="excludable-migrations"
php artisan migrate
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Maize\Excludable\ExcludableServiceProvider" --tag="excludable-config"
```

This is the content of the published config file:

```php
return [

    /*
    |--------------------------------------------------------------------------
    | Exclusion model
    |--------------------------------------------------------------------------
    |
    | Here you may specify the fully qualified class name of the exclusion model.
    |
    */

    'exclusion_model' => Maize\Excludable\Models\Exclusion::class,
];

```

## Usage

### Basic

To use the package, add the `Maize\Excludable\Excludable` trait to all models you want to make excludable.

Here's an example model including the `Excludable` trait:

``` php
<?php

namespace App\Models;

use Maize\Excludable\Excludable;

class Article extends Model
{
    use Excludable;

    protected $fillable = [
        'title',
        'body',
    ];
}
```

Now you can just query for a specific article entity and mark it as excluded.

``` php
use App\Models\Article;

$article = Article::query()->findOrFail(1)

$article->addToExclusion();

$article->excluded(); // returns true

```

That's all!

The package will add the given entity to the exclusions table, so all article related queries will exclude it.

``` php
use App\Models\Article;

Article::findOrFail(1); // throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
```

### Include excluded entities

``` php
use App\Models\Article;

Article::withExcluded()->get(); // queries all models, including those marked as excluded 
```

### Only show excluded entities

``` php
use App\Models\Article;

Article::onlyExcluded()->get(); // queries only excluded entities
```

### Event handling

The package automatically throws two separate events when excluding an entity:

- `excluding` which is thrown before the entity is actually excluded.
  This could be useful, for example, with an observer which listens to this event and does some sort of 'validation' to the related entity.
  If the given validation does not succeed, you can just return `false`, and the entity will not be excluded;
- `excluded` which is thrown right after the entity has been marked as excluded. 

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Enrico De Lazzari](https://github.com/enricodelazzari)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
