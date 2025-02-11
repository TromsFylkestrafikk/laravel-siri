<?php

namespace TromsFylkestrafikk\Siri\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class SituationValid implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->where(function (Builder $query) {
            $query->whereNull('validity_end')
                ->orWhere('validity_end', '>', now());
        });
    }
}
