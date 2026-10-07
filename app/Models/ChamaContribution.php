<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChamaContribution extends Model
{
    use BelongsToOrganization;

    protected $table = 'chama_contributions';
    protected $guarded = [];

    protected $casts = [
        'payment_date' => 'date',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(ChamaMember::class, 'member_id');
    }
}
