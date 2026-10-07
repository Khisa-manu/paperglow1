<?php

namespace App\Traits;

use App\Models\Organization;
use App\Scopes\TenantScope;

trait BelongsToOrganization
{
    protected static function bootBelongsToOrganization(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function ($model) {
            if (empty($model->organization_id) && session()->has('active_organization_id')) {
                $model->organization_id = session('active_organization_id');
            }
        });
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
