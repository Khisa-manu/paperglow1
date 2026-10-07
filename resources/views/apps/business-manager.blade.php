<x-layouts.app title="Business Manager & Invoices">
    <div class="space-y-6" x-data="{ newInvModal: false, newCustModal: false, newProdModal: false, newExpModal: false }">
        <!-- App Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold font-heading text-slate-900 tracking-tight">Business Manager & Invoices</h1>
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-red-100 text-red-800 border border-red-200">MariaDB Backed</span>
                </div>
                <p class="text-xs text-slate-500 mt-1">Manage operations, customers, sales invoices, receipts, and overhead expenses.</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center gap-2">
                <button @click="newInvModal = true" class="px-3.5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold shadow-xs transition-colors flex items-center gap-1.5">
                    <span>+</span> New Invoice
                </button>
                <button @click="newCustModal = true" class="px-3 py-2 border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-semibold transition-colors">
                    + Customer
                </button>
                <button @click="newProdModal = true" class="px-3 py-2 border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-semibold transition-colors">
                    + Product
                </button>
                <button @click="newExpModal = true" class="px-3 py-2 border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-semibold transition-colors">
                    + Expense
                </button>
            </div>
        </div>

        <!-- Metric Badges -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Sales Billed</div>
                <div class="text-xl font-bold font-heading text-slate-900">KES {{ number_format($totalSalesKes, 2) }}</div>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Paid Revenue (M-Pesa / Bank)</div>
                <div class="text-xl font-bold font-heading text-emerald-600">KES {{ number_format($totalPaidKes, 2) }}</div>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Recorded Expenses</div>
                <div class="text-xl font-bold font-heading text-slate-700">KES {{ number_format($totalExpensesKes, 2) }}</div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="border-b border-slate-200 flex items-center gap-4 text-xs font-semibold">
            <a href="{{ route('apps.business-manager', ['tab' => 'sales']) }}" class="pb-3 border-b-2 {{ $tab === 'sales' ? 'border-red-600 text-red-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                Invoices & Sales ({{ $invoices->total() }})
            </a>
            <a href="{{ route('apps.business-manager', ['tab' => 'customers']) }}" class="pb-3 border-b-2 {{ $tab === 'customers' ? 'border-red-600 text-red-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                Customers Directory ({{ $customers->count() }})
            </a>
            <a href="{{ route('apps.business-manager', ['tab' => 'inventory']) }}" class="pb-3 border-b-2 {{ $tab === 'inventory' ? 'border-red-600 text-red-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                Products & Services ({{ $products->count() }})
            </a>
            <a href="{{ route('apps.business-manager', ['tab' => 'expenses']) }}" class="pb-3 border-b-2 {{ $tab === 'expenses' ? 'border-red-600 text-red-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                Expenses & Vouchers ({{ $expenses->total() }})
            </a>
        </div>

        <!-- TAB 1: Invoices & Sales Documents -->
        @if($tab === 'sales')
            <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] border-b border-slate-200">
                            <tr>
                                <th class="p-3">Doc #</th>
                                <th class="p-3">Customer</th>
                                <th class="p-3">Issue Date</th>
                                <th class="p-3 text-right">Subtotal</th>
                                <th class="p-3 text-right">VAT (16%)</th>
                                <th class="p-3 text-right">Total (KES)</th>
                                <th class="p-3 text-center">Status</th>
                                <th class="p-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($invoices as $inv)
                                <tr class="hover:bg-slate-50">
                                    <td class="p-3 font-semibold text-slate-900 whitespace-nowrap">{{ $inv->document_number }}</td>
                                    <td class="p-3">
                                        <div class="font-medium text-slate-900">{{ $inv->customer_name }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $inv->customer_email }}</div>
                                    </td>
                                    <td class="p-3 whitespace-nowrap">{{ $inv->issue_date->format('Y-m-d') }}</td>
                                    <td class="p-3 text-right font-mono">{{ number_format($inv->subtotal, 2) }}</td>
                                    <td class="p-3 text-right font-mono text-slate-500">{{ number_format($inv->tax_amount, 2) }}</td>
                                    <td class="p-3 text-right font-mono font-bold text-slate-900">{{ number_format($inv->grand_total, 2) }}</td>
                                    <td class="p-3 text-center whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $inv->status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                            {{ $inv->status }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-right">
                                        <form action="{{ route('apps.business-manager.delete-doc', $inv) }}" method="POST" onsubmit="return confirm('Delete document?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-[11px] font-semibold">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="p-8 text-center text-slate-400">No invoices generated yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div>{{ $invoices->links() }}</div>
        @endif

        <!-- TAB 2: Customers Directory -->
        @if($tab === 'customers')
            <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] border-b border-slate-200">
                            <tr>
                                <th class="p-3">Customer Name</th>
                                <th class="p-3">Company</th>
                                <th class="p-3">Contact</th>
                                <th class="p-3">City</th>
                                <th class="p-3">KRA PIN</th>
                                <th class="p-3 text-right">Total Spent (KES)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($customers as $c)
                                <tr class="hover:bg-slate-50">
                                    <td class="p-3 font-semibold text-slate-900">{{ $c->name }}</td>
                                    <td class="p-3">{{ $c->company ?? '—' }}</td>
                                    <td class="p-3">
                                        <div>{{ $c->phone }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $c->email }}</div>
                                    </td>
                                    <td class="p-3">{{ $c->city }}</td>
                                    <td class="p-3 font-mono">{{ $c->kra_pin ?? '—' }}</td>
                                    <td class="p-3 text-right font-mono font-medium">{{ number_format($c->total_spent, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-slate-400">No customers registered yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- TAB 3: Inventory Products & Services -->
        @if($tab === 'inventory')
            <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] border-b border-slate-200">
                            <tr>
                                <th class="p-3">Item Name</th>
                                <th class="p-3">SKU</th>
                                <th class="p-3">Category</th>
                                <th class="p-3 text-center">Stock</th>
                                <th class="p-3 text-right">Cost Price (KES)</th>
                                <th class="p-3 text-right">Selling Price (KES)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($products as $p)
                                <tr class="hover:bg-slate-50">
                                    <td class="p-3 font-semibold text-slate-900">{{ $p->name }}</td>
                                    <td class="p-3 font-mono text-slate-400">{{ $p->sku ?? '—' }}</td>
                                    <td class="p-3">{{ $p->category }}</td>
                                    <td class="p-3 text-center font-medium">{{ $p->stock_quantity }} {{ $p->unit }}</td>
                                    <td class="p-3 text-right font-mono">{{ number_format($p->buying_price, 2) }}</td>
                                    <td class="p-3 text-right font-mono font-bold text-slate-900">{{ number_format($p->selling_price, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-slate-400">No products or services in catalog.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- TAB 4: Expenses -->
        @if($tab === 'expenses')
            <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] border-b border-slate-200">
                            <tr>
                                <th class="p-3">Voucher #</th>
                                <th class="p-3">Description</th>
                                <th class="p-3">Category</th>
                                <th class="p-3">Date</th>
                                <th class="p-3">Payee</th>
                                <th class="p-3 text-right">Amount (KES)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($expenses as $exp)
                                <tr class="hover:bg-slate-50">
                                    <td class="p-3 font-mono text-slate-400 font-semibold">{{ $exp->voucher_number }}</td>
                                    <td class="p-3 font-medium text-slate-900">{{ $exp->description }}</td>
                                    <td class="p-3">{{ $exp->category }}</td>
                                    <td class="p-3 whitespace-nowrap">{{ $exp->date->format('Y-m-d') }}</td>
                                    <td class="p-3">{{ $exp->payee ?? '—' }}</td>
                                    <td class="p-3 text-right font-mono font-bold text-slate-900">{{ number_format($exp->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-slate-400">No expense vouchers recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div>{{ $expenses->links() }}</div>
        @endif

        <!-- MODAL 1: Create Invoice -->
        <div x-show="newInvModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" style="display: none;">
            <div @click.away="newInvModal = false" class="bg-white rounded-xl border border-slate-200 shadow-xl max-w-xl w-full p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold font-heading text-slate-900">Generate New Sales Invoice</h3>
                    <button @click="newInvModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>
                <form action="{{ route('apps.business-manager.invoice') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="document_type" value="invoice">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Customer Name</label>
                            <input type="text" name="customer_name" required placeholder="Safaris Africa Ltd" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Customer Phone</label>
                            <input type="text" name="customer_phone" placeholder="+254 712 345 678" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Issue Date</label>
                            <input type="date" name="issue_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Payment Status</label>
                            <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 focus:outline-none bg-white">
                                <option value="unpaid">Unpaid / Issued</option>
                                <option value="paid">Paid in Full</option>
                            </select>
                        </div>
                    </div>

                    <!-- Line Item -->
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg space-y-2">
                        <div class="text-[11px] font-bold text-slate-700 uppercase">Line Item 1</div>
                        <div>
                            <input type="text" name="item_description[]" required placeholder="Item description" class="w-full px-3 py-1.5 border border-slate-300 rounded-md text-xs bg-white mb-2">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" step="1" min="1" name="item_quantity[]" value="1" required placeholder="Qty" class="w-full px-3 py-1.5 border border-slate-300 rounded-md text-xs bg-white">
                            <input type="number" step="0.01" min="0" name="item_unit_price[]" value="5000" required placeholder="Unit Price (KES)" class="w-full px-3 py-1.5 border border-slate-300 rounded-md text-xs bg-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">VAT Rate % (Kenya Standard)</label>
                        <input type="number" step="0.5" name="tax_rate" value="16" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 focus:outline-none">
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="newInvModal = false" class="px-4 py-2 border border-slate-300 rounded-lg text-xs text-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold">Generate Invoice</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 2: Create Customer -->
        <div x-show="newCustModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" style="display: none;">
            <div @click.away="newCustModal = false" class="bg-white rounded-xl border border-slate-200 shadow-xl max-w-md w-full p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold font-heading text-slate-900">Add Customer</h3>
                    <button @click="newCustModal = false" class="text-slate-400">&times;</button>
                </div>
                <form action="{{ route('apps.business-manager.customer') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Customer / Contact Name</label>
                        <input type="text" name="name" required placeholder="John Maina" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Company (Optional)</label>
                        <input type="text" name="company" placeholder="Acme Logistics" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Phone Number</label>
                        <input type="text" name="phone" placeholder="+254 712 000 000" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">KRA PIN (Kenya)</label>
                        <input type="text" name="kra_pin" placeholder="P051000000X" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 focus:outline-none">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="newCustModal = false" class="px-4 py-2 border rounded-lg text-xs text-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold">Save Customer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 3: Create Product -->
        <div x-show="newProdModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" style="display: none;">
            <div @click.away="newProdModal = false" class="bg-white rounded-xl border border-slate-200 shadow-xl max-w-md w-full p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold font-heading text-slate-900">Add Product or Service</h3>
                    <button @click="newProdModal = false" class="text-slate-400">&times;</button>
                </div>
                <form action="{{ route('apps.business-manager.product') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Name</label>
                        <input type="text" name="name" required placeholder="e.g. Branded Ceramic Mugs" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">SKU</label>
                            <input type="text" name="sku" placeholder="MUG-001" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Type</label>
                            <select name="type" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">
                                <option value="product">Physical Product</option>
                                <option value="service">Service</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Selling Price (KES)</label>
                            <input type="number" step="0.01" name="selling_price" required placeholder="850" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Cost Price (KES)</label>
                            <input type="number" step="0.01" name="buying_price" value="0" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Stock Qty</label>
                            <input type="number" name="stock_quantity" value="50" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Unit</label>
                            <input type="text" name="unit" value="pcs" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                        </div>
                    </div>
                    <input type="hidden" name="category" value="General">
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="newProdModal = false" class="px-4 py-2 border rounded-lg text-xs text-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold">Save to Catalog</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 4: Create Expense -->
        <div x-show="newExpModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" style="display: none;">
            <div @click.away="newExpModal = false" class="bg-white rounded-xl border border-slate-200 shadow-xl max-w-md w-full p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold font-heading text-slate-900">Record Overhead Expense</h3>
                    <button @click="newExpModal = false" class="text-slate-400">&times;</button>
                </div>
                <form action="{{ route('apps.business-manager.expense') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Description</label>
                        <input type="text" name="description" required placeholder="e.g. Internet & Fibre Utilities" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Category</label>
                            <input type="text" name="category" value="Utilities" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Amount (KES)</label>
                            <input type="number" step="0.01" name="amount" required placeholder="5000" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-bold">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Date</label>
                            <input type="date" name="date" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Payment Method</label>
                            <select name="payment_method" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">
                                <option value="M-Pesa">M-Pesa</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Cash">Cash</option>
                            </select>
                        </div>
                    </div>
                    <input type="hidden" name="status" value="Paid">
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="newExpModal = false" class="px-4 py-2 border rounded-lg text-xs text-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold">Record Voucher</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
