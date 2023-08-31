<?php

namespace Maize\Excludable\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class MorphManyWildcard extends MorphMany
{
    protected function getKeys(array $models, $key = null): array
    {
        return collect(parent::getKeys($models, $key))
            ->add('*')
            ->toArray();
    }

    public function one(): MorphOneWildcard
    {
        return MorphOneWildcard::noConstraints(fn () => new MorphOneWildcard(
            $this->getQuery(),
            $this->getParent(),
            $this->morphType,
            $this->foreignKey,
            $this->localKey
        ));
    }

    public function addConstraints(): void
    {
        if (static::$constraints) {
            $this
                ->getRelationQuery()
                ->where($this->morphType, $this->morphClass)
                ->whereNotNull($this->foreignKey)
                ->where(
                    fn (Builder $query) => $query
                        ->whereColumn($this->foreignKey, $this->getParentKey())
                        ->orWhere($this->foreignKey, '*')
                );
        }
    }

    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*']): Builder
    {
        if ($query->getQuery()->from == $parentQuery->getQuery()->from) {
            return $this->getRelationExistenceQueryForSelfRelation($query, $parentQuery, $columns);
        }

        return $query
            ->select($columns)
            ->where($query->qualifyColumn($this->getMorphType()), $this->morphClass)
            ->where(
                fn (Builder $query) => $query
                    ->whereColumn($this->getExistenceCompareKey(), $this->getQualifiedParentKeyName())
                    ->orWhere($this->getExistenceCompareKey(), '*')
            );
    }

    public function getRelationExistenceQueryForSelfRelation(Builder $query, Builder $parentQuery, $columns = ['*']): Builder
    {
        $query->from($query->getModel()->getTable().' as '.$hash = $this->getRelationCountHash());

        $query->getModel()->setTable($hash);

        return $query
            ->select($columns)
            ->where(
                fn (Builder $query) => $query
                    ->whereColumn($this->getForeignKeyName(), $this->getQualifiedParentKeyName())
                    ->orWhere($this->getForeignKeyName(), '*')
            );
    }
}
