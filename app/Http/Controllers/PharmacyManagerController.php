<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\PharmMedicine;
use App\Models\PharmSale;
use App\Models\PharmSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PharmacyManagerController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'inventory');
        $search = $request->query('search');

        $medicinesQuery = PharmMedicine::latest();
        $salesQuery = PharmSale::latest();
        $suppliersQuery = PharmSupplier::latest();

        if ($search) {
            $medicinesQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('generic_name', 'like', "%{$search}%")
                  ->orWhere('sku_barcode', 'like', "%{$search}%");
            });
            $salesQuery->where('receipt_number', 'like', "%{$search}%");
        }

        $medicines = $medicinesQuery->paginate(20);
        $sales = $salesQuery->paginate(15);
        $suppliers = $suppliersQuery->get();

        $totalStockCount = PharmMedicine::sum('quantity_in_stock');
        $lowStockCount = PharmMedicine::whereRaw('quantity_in_stock <= min_stock_level')->count();
        $totalSalesKes = PharmSale::sum('total_amount_kes');

        return view('apps.pharmacy-manager', compact(
            'tab',
            'medicines',
            'sales',
            'suppliers',
            'totalStockCount',
            'lowStockCount',
            'totalSalesKes',
            'search'
        ));
    }

    public function storeMedicine(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'category' => 'required|string|max:100',
            'manufacturer' => 'nullable|string|max:255',
            'sku_barcode' => 'nullable|string|max:100',
            'batch_number' => 'nullable|string|max:100',
            'unit_of_measure' => 'required|string|max:50',
            'quantity_in_stock' => 'required|integer|min:0',
            'min_stock_level' => 'required|integer|min:1',
            'buying_price_kes' => 'required|numeric|min:0',
            'selling_price_kes' => 'required|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'requires_prescription' => 'nullable|boolean',
            'shelf_location' => 'nullable|string|max:100',
        ]);

        $med = PharmMedicine::create(array_merge($validated, [
            'requires_prescription' => $request->boolean('requires_prescription'),
        ]));

        AuditLog::create([
            'organization_id' => session('active_organization_id'),
            'user_id' => Auth::id(),
            'action' => 'MEDICINE_ADDED',
            'module' => 'Pharmacy Manager',
            'details' => "Added {$med->name} (Batch: {$med->batch_number}, Qty: {$med->quantity_in_stock})",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('apps.pharmacy-manager', ['tab' => 'inventory'])
            ->with('success', "Medicine {$med->name} added to pharmacy formulary!");
    }

    public function recordSale(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'medicine_id' => 'required|exists:pharm_medicines,id',
            'quantity' => 'required|integer|min:1',
            'payment_method' => 'required|string|max:50',
            'mpesa_ref' => 'nullable|string|max:50',
        ]);

        $med = PharmMedicine::findOrFail($validated['medicine_id']);
        $qty = $validated['quantity'];

        if ($med->quantity_in_stock < $qty) {
            return back()->withErrors(['quantity' => "Insufficient stock. Only {$med->quantity_in_stock} available."]);
        }

        $unitPrice = $med->selling_price_kes;
        $totalAmount = $unitPrice * $qty;

        $receiptNumber = 'RX-' . date('Y') . '-' . rand(1000, 9999);

        PharmSale::create([
            'receipt_number' => $receiptNumber,
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'items' => [
                [
                    'name' => $med->name,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'total' => $totalAmount,
                ]
            ],
            'subtotal_kes' => $totalAmount,
            'discount_kes' => 0.00,
            'total_amount_kes' => $totalAmount,
            'payment_method' => $validated['payment_method'],
            'mpesa_ref' => $validated['mpesa_ref'],
            'dispensed_by' => Auth::user()->name,
        ]);

        $med->decrement('quantity_in_stock', $qty);

        AuditLog::create([
            'organization_id' => session('active_organization_id'),
            'user_id' => Auth::id(),
            'action' => 'MEDICINE_DISPENSED',
            'module' => 'Pharmacy Manager',
            'details' => "Receipt {$receiptNumber}: Dispensed {$qty} x {$med->name} to {$validated['customer_name']} (KES {$totalAmount})",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('apps.pharmacy-manager', ['tab' => 'sales'])
            ->with('success', "Sale receipt {$receiptNumber} completed!");
    }
}
