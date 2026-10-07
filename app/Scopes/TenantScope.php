<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     * Enforces strict multi-tenant boundary isolation.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // When running in console (migrations, seeders) without an active session context, allow unscoped execution
        if (app()->runningInConsole() && !session()->has('active_organization_id')) {
            return;
        }

        $orgId = session('active_organization_id');

        // If authenticated user and no session org set, resolve user default organization
        if (!$orgId && Auth::check()) {
            $user = Auth::user();
            $orgId = $user->default_organization_id ?? $user->organizations()->first()?->id;
            if ($orgId) {
                session(['active_organization_id' => $orgId]);
            }
        }

        // Strict Hardening: If an active tenant organization is identified, scope to that organization.
        // If NO tenant organization can be resolved for an authenticated or tenant-bound request,
        // strictly forbid unscoped execution across tenants by forcing an impossible condition (organization_id = 0).
        if ($orgId) {
            $builder->where($model->getTable() . '.organization_id', $orgId);
        } else {
            // Anti-leak hardening: prevent unscoped reads across tenants
            $builder->where($model->getTable() . '.organization_id', 0);
        }
    }
}
