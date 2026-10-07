<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class TckTicket extends Model
{
    use BelongsToOrganization;

    protected $table = 'tck_tickets';
    protected $guarded = [];

    protected $casts = [
        'due_date' => 'datetime',
        'is_overdue' => 'boolean',
    ];
}
