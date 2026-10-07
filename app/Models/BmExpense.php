<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class BmExpense extends Model
{
    use BelongsToOrganization;

    protected $table = 'bm_expenses';
    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
    ];
}
