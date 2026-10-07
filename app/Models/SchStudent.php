<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class SchStudent extends Model
{
    use BelongsToOrganization;

    protected $table = 'sch_students';
    protected $guarded = [];
}
