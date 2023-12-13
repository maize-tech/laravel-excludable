<?php

namespace Maize\Excludable\Support;

use Maize\Excludable\Models\Exclusion;
use Maize\Excludable\Queries\HasExclusionQuery;

class Config
{
    public static function getExclusionModel(): Exclusion
    {
        /** @var string $model */
        $model = config('excludable.exclusion_model') ?? Exclusion::class;

        return new $model;
    }

    public static function getHasExclusionQuery(): HasExclusionQuery
    {
        /** @var string $query */
        $query = config('excludable.has_exclusion_query') ?? HasExclusionQuery::class;

        return new $query();
    }
}
