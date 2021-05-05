<?php

namespace HFarm\Excludable;

use HFarm\Excludable\Scopes\ExclusionScope;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait Excludable
{
    public static function bootExcludable()
    {
        static::addGlobalScope(new ExclusionScope);
    }

    public function exclusion(): MorphOne
    {
        return $this->morphOne(config('excludable.exclusion_model'), 'excludable');
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
}
