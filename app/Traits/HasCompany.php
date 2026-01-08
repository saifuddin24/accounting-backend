<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasCompany
{
    public static function bootHasCompany()
    {
        static::creating(function ($model) {
            if (!$model->company_id && config('app.company_id')) {
                $model->company_id = config('app.company_id');
            }
        });

        static::addGlobalScope('company', function (Builder $builder) {
            if (config('app.company_id')) {
                $builder->where($builder->getModel()->getTable() . '.company_id', config('app.company_id'));
            }
        });
    }
}
