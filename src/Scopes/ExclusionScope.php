<?php

namespace HFarm\Excludable\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ExclusionScope implements Scope
{
    protected $extensions = ['WithExcluded', 'WithoutExcluded', 'OnlyExcluded'];

    public function apply(Builder $builder, Model $model)
    {
        $builder->doesntHave('exclusion');
    }

    public function extend(Builder $builder)
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }
    }

    protected function addWithExcluded(Builder $builder)
    {
        $builder->macro('withExcluded', function (Builder $builder, $withExcluded = true) {
            if (! $withExcluded) {
                return $builder->withoutExcluded();
            }

            return $builder->withoutGlobalScope($this);
        });
    }

    protected function addWithoutExcluded(Builder $builder)
    {
        $builder->macro('withoutExcluded', function (Builder $builder) {
            return $builder
                ->withoutGlobalScope($this)
                ->doesntHave('exclusion');
        });
    }

    protected function addOnlyExcluded(Builder $builder)
    {
        $builder->macro('onlyExcluded', function (Builder $builder) {
            return $builder
                ->withoutGlobalScope($this)
                ->has('exclusion');
        });
    }
}
