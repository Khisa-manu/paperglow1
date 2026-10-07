<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class BmProduct extends Model
{
    use BelongsToOrganization;

    protected $table = 'bm_products';
    protected $guarded = [];
}
