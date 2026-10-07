<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class BmSalesDocument extends Model
{
    use BelongsToOrganization;

    protected $table = 'bm_sales_documents';
    protected $guarded = [];

    protected $casts = [
        'items' => 'array',
        'issue_date' => 'date',
        'due_date' => 'date',
    ];
}
