<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\AuditLog;
use App\Models\OrganizationSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CatalogController extends Controller
{
    public function index()
    {
        $orgId = session('active_organization_id');
        $apps = Application::where('is_active', true)->get();
        $subscriptions = OrganizationSubscription::where('organization_id', $orgId)->get()->keyBy('app_slug');

        return view('catalog.index', compact('apps', 'subscriptions'));
    }

    public function toggle(Request $request, string $appSlug)
    {
        $orgId = session('active_organization_id');
        $app = Application::where('slug', $appSlug)->firstOrFail();

        $sub = OrganizationSubscription::where('organization_id', $orgId)
            ->where('app_slug', $appSlug)
            ->first();

        if ($sub) {
            if ($sub->status === 'active') {
                $sub->update(['status' => 'canceled']);
                $msg = "Unsubscribed from {$app->name}.";
                $action = "UNSUBSCRIBE";
            } else {
                $sub->update(['status' => 'active', 'current_period_end' => now()->addDays(30)]);
                $msg = "Activated subscription to {$app->name}!";
                $action = "SUBSCRIBE";
            }
        } else {
            OrganizationSubscription::create([
                'organization_id' => $orgId,
                'app_slug' => $appSlug,
                'plan_slug' => 'professional',
                'status' => 'active',
                'price_kes' => $app->monthly_price_kes,
                'current_period_start' => now(),
                'current_period_end' => now()->addDays(30),
            ]);
            $msg = "Subscribed to {$app->name}!";
            $action = "SUBSCRIBE";
        }

        AuditLog::create([
            'organization_id' => $orgId,
            'user_id' => Auth::id(),
            'action' => "APP_{$action}",
            'module' => 'Subscriptions',
            'details' => "{$action} for {$app->name}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->back()->with('success', $msg);
    }
}
