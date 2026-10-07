<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class InvProduct extends Model
{
    use BelongsToOrganization;

    protected $table = 'inv_products';
    protected $guarded = [];
}
