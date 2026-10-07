<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BmCustomer;
use App\Models\BmExpense;
use App\Models\BmProduct;
use App\Models\BmSalesDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessManagerController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'sales');
        $search = $request->query('search');

        $invoicesQuery = BmSalesDocument::latest();
        $customersQuery = BmCustomer::latest();
        $productsQuery = BmProduct::latest();
        $expensesQuery = BmExpense::latest();

        if ($search) {
            $invoicesQuery->where(function ($q) use ($search) {
                $q->where('document_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
            $customersQuery->where('name', 'like', "%{$search}%");
            $productsQuery->where('name', 'like', "%{$search}%");
            $expensesQuery->where('description', 'like', "%{$search}%");
        }

        $invoices = $invoicesQuery->paginate(15);
        $customers = $customersQuery->get();
        $products = $productsQuery->get();
        $expenses = $expensesQuery->paginate(15);

        $totalSalesKes = BmSalesDocument::sum('grand_total');
        $totalPaidKes = BmSalesDocument::where('status', 'paid')->sum('grand_total');
        $totalExpensesKes = BmExpense::sum('amount');

        return view('apps.business-manager', compact(
            'tab',
            'invoices',
            'customers',
            'products',
            'expenses',
            'totalSalesKes',
            'totalPaidKes',
            'totalExpensesKes',
            'search'
        ));
    }

    public function storeCustomer(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'kra_pin' => 'nullable|string|max:50',
        ]);

        $customer = BmCustomer::create($validated);

        AuditLog::create([
            'organization_id' => session('active_organization_id'),
            'user_id' => Auth::id(),
            'action' => 'CUSTOMER_CREATED',
            'module' => 'Business Manager',
            'details' => "Added customer {$customer->name}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('apps.business-manager', ['tab' => 'customers'])
            ->with('success', "Customer {$customer->name} created successfully!");
    }

    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'type' => 'required|in:product,service',
            'category' => 'required|string|max:100',
            'stock_quantity' => 'required|numeric|min:0',
            'buying_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'unit' => 'required|string|max:30',
            'description' => 'nullable|string',
        ]);

        $product = BmProduct::create($validated);

        AuditLog::create([
            'organization_id' => session('active_organization_id'),
            'user_id' => Auth::id(),
            'action' => 'PRODUCT_CREATED',
            'module' => 'Business Manager',
            'details' => "Added {$product->name} (SKU: {$product->sku})",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('apps.business-manager', ['tab' => 'inventory'])
            ->with('success', "Item {$product->name} saved to catalog!");
    }

    public function storeInvoice(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'document_type' => 'required|in:invoice,quotation,receipt',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date',
            'item_description' => 'required|array',
            'item_quantity' => 'required|array',
            'item_unit_price' => 'required|array',
            'tax_rate' => 'nullable|numeric|min:0',
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $items = [];
        $subtotal = 0;

        for ($i = 0; $i < count($validated['item_description']); $i++) {
            $desc = $validated['item_description'][$i];
            $qty = floatval($validated['item_quantity'][$i] ?? 1);
            $price = floatval($validated['item_unit_price'][$i] ?? 0);
            $total = $qty * $price;
            $subtotal += $total;

            $items[] = [
                'description' => $desc,
                'quantity' => $qty,
                'unit_price' => $price,
                'total' => $total,
            ];
        }

        $taxRate = floatval($validated['tax_rate'] ?? 16);
        $taxAmount = ($subtotal * $taxRate) / 100;
        $grandTotal = $subtotal + $taxAmount;

        $docNumber = strtoupper(substr($validated['document_type'], 0, 3)) . '-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $doc = BmSalesDocument::create([
            'document_number' => $docNumber,
            'document_type' => $validated['document_type'],
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'],
            'customer_phone' => $validated['customer_phone'],
            'issue_date' => $validated['issue_date'],
            'due_date' => $validated['due_date'] ?? $validated['issue_date'],
            'items' => $items,
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'grand_total' => $grandTotal,
            'amount_paid' => $validated['status'] === 'paid' ? $grandTotal : 0.00,
            'status' => $validated['status'],
            'notes' => $validated['notes'],
        ]);

        AuditLog::create([
            'organization_id' => session('active_organization_id'),
            'user_id' => Auth::id(),
            'action' => 'DOCUMENT_CREATED',
            'module' => 'Business Manager',
            'details' => "Created {$doc->document_type} {$doc->document_number} for {$doc->customer_name} (KES {$doc->grand_total})",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('apps.business-manager', ['tab' => 'sales'])
            ->with('success', "{$doc->document_type} {$doc->document_number} generated successfully!");
    }

    public function storeExpense(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'payee' => 'nullable|string|max:255',
            'payment_method' => 'required|string|max:50',
            'status' => 'required|string|max:30',
        ]);

        $voucherNumber = 'EXP-' . date('Y') . '-' . rand(1000, 9999);

        $expense = BmExpense::create(array_merge($validated, [
            'voucher_number' => $voucherNumber,
        ]));

        AuditLog::create([
            'organization_id' => session('active_organization_id'),
            'user_id' => Auth::id(),
            'action' => 'EXPENSE_RECORDED',
            'module' => 'Business Manager',
            'details' => "Voucher {$voucherNumber}: KES {$expense->amount} for {$expense->description}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('apps.business-manager', ['tab' => 'expenses'])
            ->with('success', "Expense voucher {$voucherNumber} recorded!");
    }

    public function deleteDocument(BmSalesDocument $doc)
    {
        // Tenant authorization handled via global scope
        $docNumber = $doc->document_number;
        $doc->delete();

        return redirect()->back()->with('success', "Document {$docNumber} deleted.");
    }
}
