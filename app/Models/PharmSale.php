<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class PharmSale extends Model
{
    use BelongsToOrganization;

    protected $table = 'pharm_sales';
    protected $guarded = [];

    protected $casts = [
        'items' => 'array',
    ];
}
