<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class ClnPatient extends Model
{
    use BelongsToOrganization;

    protected $table = 'cln_patients';
    protected $guarded = [];

    protected $casts = [
        'dob' => 'date',
    ];
}
