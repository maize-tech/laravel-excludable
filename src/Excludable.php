<?php

namespace Maize\Excludable;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\DB;
use Maize\Excludable\Scopes\ExclusionScope;
use Maize\Excludable\Support\Config;

/**
 * @method static \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder withExcluded(bool $withExcluded = true)
 * @method static \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder withoutExcluded()
 * @method static \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder onlyExcluded()
 * @method static \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder whereHasExclusion(bool $not = false)
 * @method static \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder whereDoesntHaveExclusion()
 */
trait Excludable
{
    public static function bootExcludable(): void
    {
        static::addGlobalScope(new ExclusionScope);
    }

    public function exclusion(): MorphOne
    {
        return $this->morphOne(
            related: Config::getExclusionModel(),
            name: 'excludable'
        );
    }

    public function excluded(): bool
    {
        return $this->exclusion()->exists();
    }

    public function addToExclusion(): bool
    {
        if ($this->fireModelEvent('excluding') === false) {
            return false;
        }

        $exclusion = $this->exclusion()->firstOrCreate([
            'excludable_type' => $this->getMorphClass(),
            'excludable_id' => $this->getKey(),
        ]);

        if ($exclusion->wasRecentlyCreated) {
            $this->fireModelEvent('excluded', false);
        }

        return true;
    }

    public function removeFromExclusion(): bool
    {
        $this->exclusion()->delete();

        return true;
    }

    public static function excludeAllModels(): void
    {
        DB::transaction(function () {
            Config::getExclusionModel()
                ->query()
                ->where('excludable_type', app(static::class)->getMorphClass())
                ->delete();

            Config::getExclusionModel()
                ->query()
                ->create([
                    'excludable_type' => app(static::class)->getMorphClass(),
                    'excludable_id' => '*',
                ]);
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
