<?php

namespace Maize\Excludable\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait HasWildcardRelationships
{
    public function morphOneWildcard(string|Model $related, string $name, ?string $type = null, ?string $id = null, ?string $localKey = null): MorphOneWildcard
    {
        $instance = $this->newRelatedInstance($related);

        [$type, $id] = $this->getMorphs($name, $type, $id);

        $table = $instance->getTable();

        $localKey = $localKey ?: $this->getKeyName();

        return $this->newMorphOneWildcard($instance->newQuery(), $this, $table.'.'.$type, $table.'.'.$id, $localKey);
    }

    protected function newMorphOneWildcard(Builder $query, Model $parent, string $type, string $id, string $localKey): MorphOneWildcard
    {
        return new MorphOneWildcard($query, $parent, $type, $id, $localKey);
    }

    public function morphManyWildcard(string|Model $related, string $name, ?string $type = null, ?string $id = null, ?string $localKey = null): MorphManyWildcard
    {
        $instance = $this->newRelatedInstance($related);

        [$type, $id] = $this->getMorphs($name, $type, $id);

        $table = $instance->getTable();

        $localKey = $localKey ?: $this->getKeyName();

        return $this->newMorphManyWildcard($instance->newQuery(), $this, $table.'.'.$type, $table.'.'.$id, $localKey);
    }

    protected function newMorphManyWildcard(Builder $query, Model $parent, string $type, string $id, string $localKey): MorphManyWildcard
    {
        return new MorphManyWildcard($query, $parent, $type, $id, $localKey);
    }
}
