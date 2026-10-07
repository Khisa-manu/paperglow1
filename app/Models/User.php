<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'organization_members')
            ->withPivot('role_id', 'role_name', 'title', 'status')
            ->withTimestamps();
    }

    public function currentOrganization(): ?Organization
    {
        $orgId = session('active_organization_id');
        if ($orgId) {
            $org = $this->organizations()->where('organizations.id', $orgId)->first();
            if ($org) {
                return $org;
            }
        }

        $first = $this->organizations()->first();
        if ($first) {
            session(['active_organization_id' => $first->id]);
            return $first;
        }

        return null;
    }
}
