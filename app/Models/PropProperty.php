<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PropProperty extends Model
{
    use BelongsToOrganization;

    protected $table = 'prop_properties';
    protected $guarded = [];

    public function units(): HasMany
    {
        return $this->hasMany(PropUnit::class, 'property_id');
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(PropTenant::class, 'property_id');
    }
}
