<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use BelongsToOrganization;

    protected $guarded = [];

    protected $casts = [
        'read_at' => 'datetime',
    ];
}
