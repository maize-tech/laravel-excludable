<?php

namespace Maize\Excludable\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Maize\Excludable\Models\Exclusion;
use Maize\Excludable\Support\Config;

class HasExclusionQuery
{
    public function __invoke(Builder $builder, $not = false): Builder
    {
        $model = $builder->getModel();
        $exclusionModel = Config::getExclusionModel();

        return $builder
            ->whereHas(
                relation: 'exclusion',
                operator: $not ? '<' : '>='
            )
            ->whereIn(
                column: $model->getQualifiedKeyName(),
                values: fn (QueryBuilder $query) => $query
                    ->select($exclusionModel->qualifyColumn('excludable_id'))
                    ->from($exclusionModel->getTable())
                    ->where($exclusionModel->qualifyColumn('type'), Exclusion::TYPE_INCLUDE)
                    ->where($exclusionModel->qualifyColumn('excludable_type'), $model->getMorphClass())
                    ->whereColumn($exclusionModel->qualifyColumn('excludable_id'), $model->getQualifiedKeyName()),
                boolean: $not ? 'or' : 'and',
                not: ! $not
            );
    }
}
