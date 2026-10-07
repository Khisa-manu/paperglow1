<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OrganizationController extends Controller
{
    public function switch(Organization $organization)
    {
        $user = Auth::user();

        // Enforce Server-Side Authorization: User must be a member of this org
        $isMember = $user->organizations()->where('organizations.id', $organization->id)->exists();
        if (!$isMember) {
            abort(403, 'Unauthorized. You are not a member of this organization.');
        }

        session(['active_organization_id' => $organization->id]);

        \App\Models\AuditLog::create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'action' => 'ORGANIZATION_SWITCHED',
            'module' => 'Workspaces',
            'details' => "Switched active workspace to {$organization->name}",
            'ip_address' => request()->ip(),
        ]);

        return redirect()->back()->with('success', "Switched workspace to {$organization->name}");
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:50',
        ]);

        $user = Auth::user();

        $org = Organization::create([
            'uuid' => (string) Str::uuid(),
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name'] . '-' . rand(100, 999)),
            'billing_email' => $user->email,
            'city' => $validated['city'] ?? 'Nairobi',
            'tax_id' => $validated['tax_id'] ?? null,
            'plan_tier' => 'starter',
            'status' => 'active',
        ]);

        $ownerRole = \App\Models\Role::where('name', 'owner')->first();
        $user->organizations()->attach($org->id, [
            'role_id' => $ownerRole ? $ownerRole->id : null,
            'role_name' => 'owner',
            'title' => 'Workspace Owner',
            'status' => 'active',
        ]);

        // Subscribe to initial apps
        $defaultApps = ['business-manager', 'invoices', 'ticketing'];
        foreach ($defaultApps as $slug) {
            \App\Models\OrganizationSubscription::create([
                'organization_id' => $org->id,
                'app_slug' => $slug,
                'status' => 'active',
                'price_kes' => 3500.00,
            ]);
        }

        session(['active_organization_id' => $org->id]);

        return redirect()->route('dashboard')->with('success', "Workspace {$org->name} created successfully!");
    }

    public function members()
    {
        $orgId = session('active_organization_id');
        $org = Organization::with('users')->findOrFail($orgId);
        $roles = \App\Models\Role::all();

        return view('organizations.members', compact('org', 'roles'));
    }

    public function addMember(Request $request)
    {
        $orgId = session('active_organization_id');
        $org = Organization::findOrFail($orgId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role_name' => 'required|string',
            'title' => 'nullable|string|max:100',
        ]);

        // Find or create user with a secure randomly generated password
        $memberUser = User::firstOrCreate(
            ['email' => $validated['email']],
            [
                'name' => $validated['name'],
                'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(16)),
            ]
        );

        $role = \App\Models\Role::where('name', $validated['role_name'])->first();

        $org->users()->syncWithoutDetaching([
            $memberUser->id => [
                'role_id' => $role ? $role->id : null,
                'role_name' => $validated['role_name'],
                'title' => $validated['title'] ?? 'Team Member',
                'status' => 'active',
            ]
        ]);

        \App\Models\AuditLog::create([
            'organization_id' => $org->id,
            'user_id' => Auth::id(),
            'action' => 'MEMBER_ADDED',
            'module' => 'Team',
            'details' => "Added {$validated['name']} ({$validated['email']}) as {$validated['role_name']}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->back()->with('success', "Member {$validated['name']} added to {$org->name}!");
    }
}
