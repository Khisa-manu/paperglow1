<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class BkService extends Model
{
    use BelongsToOrganization;

    protected $table = 'bk_services';
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
