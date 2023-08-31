<?php

namespace Maize\Excludable\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Query\JoinClause;

class MorphOneWildcard extends MorphOne
{
    public function addOneOfManyJoinSubQueryConstraints(JoinClause $join): void
    {
        $join
            ->on($this->qualifySubSelectColumn($this->morphType), '=', $this->qualifyRelatedColumn($this->morphType))
            ->where(
                fn (Builder $query) => $query
                    ->whereColumn($this->qualifySubSelectColumn($this->foreignKey), $this->qualifyRelatedColumn($this->foreignKey))
                    ->orWhere($this->qualifySubSelectColumn($this->foreignKey), '*')
            );
    }

    public function addConstraints(): void
    {
        if (static::$constraints) {
            $query = $this->getRelationQuery();

            $query
                ->whereNotNull($this->foreignKey)
                ->where(
                    fn (Builder $query) => $query
                        ->whereColumn($this->foreignKey, $this->getParentKey())
                        ->orWhere($this->foreignKey, '*')
                );
        }
    }

    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, mixed $columns = ['*']): Builder
    {
        return $query
            ->select($columns)
            ->where($query->qualifyColumn($this->getMorphType()), $this->morphClass)
            ->where(
                fn (Builder $query) => $query
                    ->whereColumn($this->getExistenceCompareKey(), $this->getQualifiedParentKeyName())
                    ->orWhere($this->getExistenceCompareKey(), '*')
            );
    }
}
