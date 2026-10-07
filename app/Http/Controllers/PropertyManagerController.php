<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\PropMaintenanceTicket;
use App\Models\PropProperty;
use App\Models\PropRentPayment;
use App\Models\PropTenant;
use App\Models\PropUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropertyManagerController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'properties');
        $search = $request->query('search');

        $propertiesQuery = PropProperty::with(['units', 'tenants'])->latest();
        $tenantsQuery = PropTenant::with('property')->latest();
        $paymentsQuery = PropRentPayment::latest();
        $ticketsQuery = PropMaintenanceTicket::latest();

        if ($search) {
            $propertiesQuery->where('name', 'like', "%{$search}%");
            $tenantsQuery->where('name', 'like', "%{$search}%");
            $paymentsQuery->where('receipt_number', 'like', "%{$search}%");
        }

        $properties = $propertiesQuery->get();
        $tenants = $tenantsQuery->get();
        $payments = $paymentsQuery->paginate(15);
        $tickets = $ticketsQuery->get();

        $totalUnits = PropUnit::count();
        $occupiedUnits = PropUnit::where('status', 'occupied')->count();
        $totalRentCollectedKes = PropRentPayment::sum('amount_kes');

        return view('apps.property-manager', compact(
            'tab',
            'properties',
            'tenants',
            'payments',
            'tickets',
            'totalUnits',
            'occupiedUnits',
            'totalRentCollectedKes',
            'search'
        ));
    }

    public function storeProperty(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'property_type' => 'required|string',
            'location' => 'required|string|max:255',
            'county' => 'nullable|string|max:100',
            'total_units' => 'required|integer|min:1',
            'caretaker_name' => 'nullable|string|max:255',
            'caretaker_phone' => 'nullable|string|max:50',
            'amenities' => 'nullable|string',
        ]);

        $prop = PropProperty::create($validated);

        // Auto-generate units
        for ($i = 1; $i <= min($prop->total_units, 20); $i++) {
            PropUnit::create([
                'property_id' => $prop->id,
                'unit_number' => 'Unit ' . $i,
                'floor' => ceil($i / 4),
                'unit_type' => '2 Bedroom Master Ensuite',
                'monthly_rent_kes' => 45000.00,
                'deposit_kes' => 45000.00,
                'status' => 'vacant',
            ]);
        }

        AuditLog::create([
            'organization_id' => session('active_organization_id'),
            'user_id' => Auth::id(),
            'action' => 'PROPERTY_CREATED',
            'module' => 'Property Manager',
            'details' => "Added property {$prop->name} with {$prop->total_units} units",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('apps.property-manager', ['tab' => 'properties'])
            ->with('success', "Property {$prop->name} added to portfolio!");
    }

    public function storeTenant(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:prop_properties,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'national_id' => 'nullable|string|max:50',
            'monthly_rent_kes' => 'required|numeric|min:0',
            'move_in_date' => 'required|date',
            'emergency_contact' => 'nullable|string|max:255',
        ]);

        $tenant = PropTenant::create(array_merge($validated, [
            'status' => 'active',
            'balance_kes' => 0.00,
        ]));

        AuditLog::create([
            'organization_id' => session('active_organization_id'),
            'user_id' => Auth::id(),
            'action' => 'TENANT_REGISTERED',
            'module' => 'Property Manager',
            'details' => "Registered tenant {$tenant->name} (Rent: KES {$tenant->monthly_rent_kes})",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('apps.property-manager', ['tab' => 'tenants'])
            ->with('success', "Tenant {$tenant->name} registered successfully!");
    }

    public function storePayment(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:prop_tenants,id',
            'amount_kes' => 'required|numeric|min:1',
            'month_for' => 'required|string|max:100',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|max:50',
            'transaction_reference' => 'nullable|string|max:100',
        ]);

        $tenant = PropTenant::findOrFail($validated['tenant_id']);
        $receiptNumber = 'RCT-' . date('Y') . '-' . rand(1000, 9999);

        PropRentPayment::create(array_merge($validated, [
            'property_id' => $tenant->property_id,
            'unit_id' => $tenant->unit_id,
            'receipt_number' => $receiptNumber,
            'status' => 'confirmed',
        ]));

        AuditLog::create([
            'organization_id' => session('active_organization_id'),
            'user_id' => Auth::id(),
            'action' => 'RENT_PAYMENT_RECORDED',
            'module' => 'Property Manager',
            'details' => "Receipt {$receiptNumber}: KES {$validated['amount_kes']} from {$tenant->name} for {$validated['month_for']}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('apps.property-manager', ['tab' => 'payments'])
            ->with('success', "Rent payment receipt {$receiptNumber} generated!");
    }
}
