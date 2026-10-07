<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropTenant extends Model
{
    use BelongsToOrganization;

    protected $table = 'prop_tenants';
    protected $guarded = [];

    public function property(): BelongsTo
    {
        return $this->belongsTo(PropProperty::class, 'property_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(PropUnit::class, 'unit_id');
    }
}
