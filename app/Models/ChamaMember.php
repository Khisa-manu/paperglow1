<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChamaMember extends Model
{
    use BelongsToOrganization;

    protected $table = 'chama_members';
    protected $guarded = [];

    public function group(): BelongsTo
    {
        return $this->belongsTo(ChamaGroup::class, 'group_id');
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(ChamaContribution::class, 'member_id');
    }
}
