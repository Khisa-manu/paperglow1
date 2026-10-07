<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            $org = $user->organizations()->first();
            if ($org) {
                session(['active_organization_id' => $org->id]);
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'organization_name' => 'required|string|max:255',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $org = Organization::create([
            'uuid' => (string) Str::uuid(),
            'name' => $validated['organization_name'],
            'slug' => Str::slug($validated['organization_name'] . '-' . rand(100, 999)),
            'billing_email' => $validated['email'],
            'city' => 'Nairobi',
            'county_state' => 'Nairobi County',
            'country_code' => 'KE',
            'preferred_currency' => 'KES',
            'plan_tier' => 'free_trial',
            'status' => 'active',
        ]);

        $ownerRole = \App\Models\Role::where('name', 'owner')->first();

        $user->organizations()->attach($org->id, [
            'role_id' => $ownerRole ? $ownerRole->id : null,
            'role_name' => 'owner',
            'title' => 'Founder / Owner',
            'status' => 'active',
        ]);

        // Subscribe to initial default apps
        $defaultApps = ['business-manager', 'invoices', 'crm', 'property-manager', 'pharmacy-manager', 'chama-manager', 'ticketing', 'booking', 'stock-inventory'];
        foreach ($defaultApps as $appSlug) {
            \App\Models\OrganizationSubscription::create([
                'organization_id' => $org->id,
                'app_slug' => $appSlug,
                'plan_slug' => 'professional',
                'status' => 'active',
                'price_kes' => 3500.00,
                'current_period_start' => now(),
                'current_period_end' => now()->addDays(30),
            ]);
        }

        // Add welcome notification
        \App\Models\Notification::create([
            'organization_id' => $org->id,
            'user_id' => $user->id,
            'type' => 'welcome',
            'title' => 'Welcome to Paperglow SaaS!',
            'message' => 'Your workspace ' . $org->name . ' is initialized with PHP 8.3 & MariaDB persistence.',
            'category' => 'system',
            'action_url' => route('dashboard'),
        ]);

        Auth::login($user);
        session(['active_organization_id' => $org->id]);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
