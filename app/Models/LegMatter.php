<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class LegMatter extends Model
{
    use BelongsToOrganization;

    protected $table = 'leg_matters';
    protected $guarded = [];

    protected $casts = [
        'filing_date' => 'date',
        'next_court_date' => 'date',
    ];
}
