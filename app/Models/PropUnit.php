<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropUnit extends Model
{
    use BelongsToOrganization;

    protected $table = 'prop_units';
    protected $guarded = [];

    public function property(): BelongsTo
    {
        return $this->belongsTo(PropProperty::class, 'property_id');
    }
}
