<?php

namespace Maize\Excludable\Support;

use Maize\Excludable\Models\Exclusion;

class Config
{
    public static function getExclusionModel(): Exclusion
    {
        $model = config('excludable.exclusion_model') ?? Exclusion::class;

        return new $model;
    }
}
