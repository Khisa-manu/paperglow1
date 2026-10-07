<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChamaGroup extends Model
{
    use BelongsToOrganization;

    protected $table = 'chama_groups';
    protected $guarded = [];

    public function members(): HasMany
    {
        return $this->hasMany(ChamaMember::class, 'group_id');
    }
}
