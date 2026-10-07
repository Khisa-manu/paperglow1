<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\InvProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockInventoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $query = InvProduct::latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $products = $query->paginate(20);
        $totalItems = InvProduct::count();
        $totalUnits = InvProduct::sum('current_quantity');
        $lowStockCount = InvProduct::whereRaw('current_quantity <= min_stock_level')->count();

        return view('apps.stock-inventory', compact(
            'products',
            'totalItems',
            'totalUnits',
            'lowStockCount',
            'search'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:100',
            'category' => 'required|string|max:100',
            'supplier_name' => 'nullable|string|max:255',
            'current_quantity' => 'required|integer|min:0',
            'min_stock_level' => 'required|integer|min:1',
            'max_stock_level' => 'required|integer|min:1',
            'buying_price_kes' => 'required|numeric|min:0',
            'selling_price_kes' => 'required|numeric|min:0',
            'unit' => 'required|string|max:30',
            'location' => 'nullable|string|max:100',
        ]);

        $status = ($validated['current_quantity'] <= $validated['min_stock_level']) ? 'low_stock' : 'in_stock';

        $product = InvProduct::create(array_merge($validated, [
            'status' => $status,
        ]));

        AuditLog::create([
            'organization_id' => session('active_organization_id'),
            'user_id' => Auth::id(),
            'action' => 'INVENTORY_PRODUCT_ADDED',
            'module' => 'Stock Inventory',
            'details' => "Added {$product->name} (SKU: {$product->sku}, Qty: {$product->current_quantity})",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('apps.stock-inventory')->with('success', "Product {$product->name} registered in warehouse!");
    }

    public function adjustStock(Request $request, InvProduct $product)
    {
        $validated = $request->validate([
            'quantity_change' => 'required|integer',
            'reason' => 'required|string|max:255',
        ]);

        $oldQty = $product->current_quantity;
        $newQty = max(0, $oldQty + $validated['quantity_change']);
        $status = ($newQty <= $product->min_stock_level) ? 'low_stock' : 'in_stock';

        $product->update([
            'current_quantity' => $newQty,
            'status' => $status,
        ]);

        AuditLog::create([
            'organization_id' => session('active_organization_id'),
            'user_id' => Auth::id(),
            'action' => 'STOCK_ADJUSTMENT',
            'module' => 'Stock Inventory',
            'details' => "Adjusted {$product->name}: {$oldQty} -> {$newQty} ({$validated['reason']})",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->back()->with('success', "Stock for {$product->name} updated to {$newQty} {$product->unit}!");
    }
}
