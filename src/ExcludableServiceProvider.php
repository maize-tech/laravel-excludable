<?php

namespace Maize\Excludable;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ExcludableServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-excludable')
            ->hasConfigFile()
            ->hasMigration('create_exclusions_table');
    }
}
