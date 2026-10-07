<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class BmCustomer extends Model
{
    use BelongsToOrganization;

    protected $table = 'bm_customers';
    protected $guarded = [];
}
