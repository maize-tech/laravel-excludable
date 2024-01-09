<?php

namespace Maize\Excludable\Tests;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Maize\Excludable\ExcludableServiceProvider;
use Maize\Excludable\Models\Exclusion;
use Maize\Excludable\Tests\Models\Article;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Maize\\Excludable\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            ExcludableServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        include_once __DIR__.'/../database/migrations/create_exclusions_table.php.stub';
        (new \CreateExclusionsTable())->up();

        include_once __DIR__.'/../database/migrations/create_articles_table.php.stub';
        (new \CreateArticlesTable())->up();
    }

    public function assertModelCount(string $model, int $count)
    {
        return $this->assertDatabaseCount((new $model())->getTable(), $count);
    }

    public function assertModelsCount(int $exclusions, int $articles)
    {
        return $this
            ->assertModelCount(Exclusion::class, $exclusions)
            ->assertModelCount(Article::class, $articles);
    }

    public function assertExcludableHas(Article|Collection|array $model, array $data = [], int $take = 0)
    {
        $models = Collection::wrap($model)->collect();

        if ($take > 0) {
            $models = $models->take($take);
        }

        $table = (new Exclusion())->getTable();
        $models->each(fn ($model) => $this->assertDatabaseHas($table, array_merge([
            'excludable_type' => $model->getMorphClass(),
            'excludable_id' => $model->getKey(),
        ], $data)));

        return $this;
    }

    public function assertExcludableHasWildcard(string $model, array $data = [])
    {
        $table = (new Exclusion())->getTable();

        return $this->assertDatabaseHas($table, array_merge([
            'excludable_type' => $model,
            'excludable_id' => '*',
        ], $data));
    }

    public function assertExcludableMissing(Article|Collection|array $model, int $shift = 0)
    {
        $models = Collection::wrap($model)->collect();

        if ($shift > 0) {
            $models->shift($shift);
        }

        $table = (new Exclusion())->getTable();
        $models->each(fn ($model) => $this->assertDatabaseMissing($table, [
            'excludable_type' => $model->getMorphClass(),
            'excludable_id' => $model->getKey(),
        ]));

        return $this;
    }

    public function assertQueryCount(int $count, Builder $query)
    {
        $this->assertCount($count, $query->get());

        return $this;
    }
}
