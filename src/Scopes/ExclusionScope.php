<?php

namespace Maize\Excludable\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Maize\Excludable\Models\Exclusion;
use Maize\Excludable\Support\Config;

class ExclusionScope implements Scope
{
    protected array $extensions = ['WhereHasExclusion', 'WhereDoesntHaveExclusion', 'WithExcluded', 'WithoutExcluded', 'OnlyExcluded'];

    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereDoesntHaveExclusion();
    }

    public function extend(Builder $builder): void
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }
    }

    protected function addWhereHasExclusion(Builder $builder): void
    {
        $builder->macro('whereHasExclusion', function (Builder $builder, $not = false) {
            $model = $builder->getModel();
            $exclusionModel = Config::getExclusionModel();

            return $builder
                ->whereExists(
                    callback: fn (QueryBuilder $query) => $query
                        ->select(DB::raw(1))
                        ->from($exclusionModel->getTable())
                        ->where('type', Exclusion::TYPE_EXCLUDE)
                        ->where($exclusionModel->qualifyColumn('excludable_type'), $model->getMorphClass())
                        ->where(
                            fn (QueryBuilder $query) => $query
                                ->whereColumn($exclusionModel->qualifyColumn('excludable_id'), $model->getQualifiedKeyName())
                                ->orWhere($exclusionModel->qualifyColumn('excludable_id'), '*')
                        ),
                    not: $not
                )
                ->whereIn(
                    column: $model->getQualifiedKeyName(),
                    values: fn (QueryBuilder $query) => $query
                        ->select($exclusionModel->qualifyColumn('excludable_id'))
                        ->from($exclusionModel->getTable())
                        ->where('type', Exclusion::TYPE_INCLUDE)
                        ->where($exclusionModel->qualifyColumn('excludable_type'), $model->getMorphClass())
                        ->whereColumn($exclusionModel->qualifyColumn('excludable_id'), $model->getQualifiedKeyName()),
                    boolean: $not ? 'or' : 'and',
                    not: ! $not
                );
        });
    }

    protected function addWhereDoesntHaveExclusion(Builder $builder): void
    {
        $builder->macro('whereDoesntHaveExclusion', function (Builder $builder) {
            return $builder->whereHasExclusion(not: true);
        });
    }

    protected function addWithExcluded(Builder $builder): void
    {
        $builder->macro('withExcluded', function (Builder $builder, $withExcluded = true) {
            if (! $withExcluded) {
                return $builder->withoutExcluded();
            }

            return $builder->withoutGlobalScope($this);
        });
    }

    protected function addWithoutExcluded(Builder $builder): void
    {
        $builder->macro('withoutExcluded', function (Builder $builder) {
            return $builder
                ->withoutGlobalScope($this)
                ->whereDoesntHaveExclusion();
        });
    }

    protected function addOnlyExcluded(Builder $builder): void
    {
        $builder->macro('onlyExcluded', function (Builder $builder) {
            return $builder
                ->withoutGlobalScope($this)
                ->whereHasExclusion();
        });
    }
}
