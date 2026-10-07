<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class EnforceOrganization
{
    public function handle(Request $request, Closure $next): Response
    {
        // Never enforce organization context on unauthenticated / guest requests
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        if ($user) {
            $userOrgs = $user->organizations;

            if ($userOrgs->isEmpty()) {
                // Provision fallback organization if user has none
                $org = \App\Models\Organization::create([
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'name' => $user->name . "'s Workspace",
                    'slug' => \Illuminate\Support\Str::slug($user->name . '-workspace-' . rand(100, 999)),
                    'billing_email' => $user->email,
                    'city' => 'Nairobi',
                    'county_state' => 'Nairobi County',
                    'country_code' => 'KE',
                    'preferred_currency' => 'KES',
                    'plan_tier' => 'free_trial',
                    'status' => 'active',
                ]);

                $user->organizations()->attach($org->id, [
                    'role_name' => 'owner',
                    'title' => 'Owner',
                    'status' => 'active',
                ]);

                session(['active_organization_id' => $org->id]);
                $userOrgs = $user->organizations()->get();
            }

            $activeOrgId = session('active_organization_id');
            $activeOrg = $userOrgs->firstWhere('id', $activeOrgId);

            if (!$activeOrg) {
                $activeOrg = $userOrgs->first();
                session(['active_organization_id' => $activeOrg->id]);
            }

            // Share context globally with all Blade views
            $notifications = \App\Models\Notification::where('organization_id', $activeOrg->id)
                ->whereNull('read_at')
                ->latest()
                ->take(5)
                ->get();

            $unreadCount = \App\Models\Notification::where('organization_id', $activeOrg->id)
                ->whereNull('read_at')
                ->count();

            $subscribedApps = \App\Models\OrganizationSubscription::where('organization_id', $activeOrg->id)
                ->whereIn('status', ['active', 'trial'])
                ->pluck('app_slug')
                ->toArray();

            View::share('currentOrg', $activeOrg);
            View::share('userOrgs', $userOrgs);
            View::share('notifications', $notifications);
            View::share('unreadCount', $unreadCount);
            View::share('subscribedApps', $subscribedApps);
        }

        return $next($request);
    }
}
