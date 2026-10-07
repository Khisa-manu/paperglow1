<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class BkBooking extends Model
{
    use BelongsToOrganization;

    protected $table = 'bk_bookings';
    protected $guarded = [];

    protected $casts = [
        'booking_date' => 'date',
    ];
}
