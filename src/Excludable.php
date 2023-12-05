<?php

namespace Maize\Excludable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Maize\Excludable\Models\Exclusion;
use Maize\Excludable\Scopes\ExclusionScope;
use Maize\Excludable\Support\Config;
use Maize\Excludable\Support\HasWildcardRelationships;
use Maize\Excludable\Support\MorphManyWildcard;
use Maize\Excludable\Support\MorphOneWildcard;

/**
 * @method static \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder withExcluded(bool $withExcluded = true)
 * @method static \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder withoutExcluded()
 * @method static \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder onlyExcluded()
 * @method static \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder whereHasExclusion(bool $not = false)
 * @method static \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder whereDoesntHaveExclusion()
 */
trait Excludable
{
    use HasWildcardRelationships;

    public static function bootExcludable(): void
    {
        static::addGlobalScope(new ExclusionScope);
    }

    public function exclusions(): MorphManyWildcard
    {
        return $this
            ->morphManyWildcard(
                related: Config::getExclusionModel(),
                name: 'excludable'
            );
    }

    public function exclusion(): MorphOneWildcard
    {
        return $this
            ->exclusions()
            ->where('type', Exclusion::TYPE_EXCLUDE)
            ->one();
    }

    public function excluded(): bool
    {
        return $this->exclusions()->count() === 1;
    }

    public static function areAllExcluded(): bool
    {
        return Config::getExclusionModel()
            ->query()
            ->where('excludable_type', app(static::class)->getMorphClass())
            ->where('excludable_id', '*')
            ->exists();
    }

    public function addToExclusion(): bool
    {
        return DB::transaction(function () {
            $this->exclusions()->where([
                'type' => Exclusion::TYPE_INCLUDE,
                'excludable_type' => $this->getMorphClass(),
                'excludable_id' => $this->getKey(),
            ])->delete();

            if ($this->excluded()) {
                return true;
            }

            if ($this->fireModelEvent('excluding') === false) {
                return false;
            }

            $exclusion = $this->exclusion()->firstOrCreate([
                'type' => Exclusion::TYPE_EXCLUDE,
                'excludable_type' => $this->getMorphClass(),
                'excludable_id' => $this->getKey(),
            ]);

            if ($exclusion->wasRecentlyCreated) {
                $this->fireModelEvent('excluded', false);
            }

            return true;
        });
    }

    public function removeFromExclusion(): bool
    {
        return DB::transaction(function () {
            if (! $this->excluded()) {
                return false;
            }

            $this->exclusion()
                ->where('excludable_id', '!=', '*')
                ->delete();

            if (! static::areAllExcluded()) {
                return false;
            }

            Config::getExclusionModel()->create([
                'type' => Exclusion::TYPE_INCLUDE,
                'excludable_type' => $this->getMorphClass(),
                'excludable_id' => $this->getKey(),
            ]);

            return true;
        });
    }

    public static function excludeAllModels(array|Model $exceptions = []): void
    {
        $exceptions = collect($exceptions)
            ->map(fn (mixed $exception) => match (true) {
                is_a($exception, Model::class) => $exception->getKey(),
                default => $exception,
            });

        DB::transaction(function () use ($exceptions) {
            $exclusionModel = Config::getExclusionModel();

            $exclusionModel
                ->query()
                ->where('excludable_type', app(static::class)->getMorphClass())
                ->delete();

            $exclusionModel
                ->query()
                ->create([
                    'type' => Exclusion::TYPE_EXCLUDE,
                    'excludable_type' => app(static::class)->getMorphClass(),
                    'excludable_id' => '*',
                ]);

            $exceptions->each(
                fn (mixed $exception) => $exclusionModel->query()->create([
                    'type' => Exclusion::TYPE_INCLUDE,
                    'excludable_type' => app(static::class)->getMorphClass(),
                    'excludable_id' => $exception,
                ])
            );
        });
    }

    public static function includeAllModels(): void
    {
        Config::getExclusionModel()
            ->query()
            ->where('excludable_type', app(static::class)->getMorphClass())
            ->delete();
    }
}
