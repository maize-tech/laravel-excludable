<?php

namespace Maize\Excludable\Support;

use Maize\Excludable\Models\Exclusion;

class Config
{
    public static function getExclusionModel(): Exclusion
    {
        /** @var string $model */
        $model = config('excludable.exclusion_model') ?? Exclusion::class;

        return new $model;
    }
}
