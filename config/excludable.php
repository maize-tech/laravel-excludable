<?php

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

    /*
    |--------------------------------------------------------------------------
    | Has exclusion query
    |--------------------------------------------------------------------------
    |
    | Here you may specify the fully qualified class name of the exclusion query.
    |
    */

    'has_exclusion_query' => Maize\Excludable\Queries\HasExclusionQuery::class,
];
