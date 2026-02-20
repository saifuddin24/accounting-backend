<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasProfile
{
    public static function bootHasProfile()
    {
        static::creating(function ($model) {
            if (!$model->profile_id && config('accounting.profile_id')) {
                $model->profile_id = config('accounting.profile_id');
            }
        });

        static::addGlobalScope('profile', function (Builder $builder) {
            if (config('accounting.profile_id')) {
                $builder->where($builder->getModel()->getTable() . '.profile_id', config('accounting.profile_id'));
            }
        });
    }

    public function profile(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Profile::class);
    }
}
