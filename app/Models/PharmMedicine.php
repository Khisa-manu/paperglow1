<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class PharmMedicine extends Model
{
    use BelongsToOrganization;

    protected $table = 'pharm_medicines';
    protected $guarded = [];

    protected $casts = [
        'expiry_date' => 'date',
        'requires_prescription' => 'boolean',
    ];
}
