<x-layouts.app title="Pharmacy Manager">
    <div class="space-y-6" x-data="{ newMedModal: false, newSaleModal: false }">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold font-heading text-slate-900 tracking-tight">Pharmacy Manager</h1>
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-teal-100 text-teal-800 border border-teal-200">Dispensary POS</span>
                </div>
                <p class="text-xs text-slate-500 mt-1">PPB compliant dispensary management, batch expiry tracking, and counter sales.</p>
            </div>

            <div class="flex items-center gap-2">
                <button @click="newSaleModal = true" class="px-3.5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold shadow-xs transition-colors flex items-center gap-1.5">
                    <span>+</span> Dispense / Sale
                </button>
                <button @click="newMedModal = true" class="px-3 py-2 border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-semibold transition-colors">
                    + Add Medicine
                </button>
            </div>
        </div>

        <!-- Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Stock Count</div>
                <div class="text-xl font-bold font-heading text-slate-900">{{ number_format($totalStockCount) }} Units</div>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Low Stock Alerts</div>
                <div class="text-xl font-bold font-heading text-amber-600">{{ $lowStockCount }} Reorder items</div>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Dispensary Revenue</div>
                <div class="text-xl font-bold font-heading text-slate-900">KES {{ number_format($totalSalesKes, 2) }}</div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="border-b border-slate-200 flex items-center gap-4 text-xs font-semibold">
            <a href="{{ route('apps.pharmacy-manager', ['tab' => 'inventory']) }}" class="pb-3 border-b-2 {{ $tab === 'inventory' ? 'border-red-600 text-red-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                Medicines Inventory ({{ $medicines->total() }})
            </a>
            <a href="{{ route('apps.pharmacy-manager', ['tab' => 'sales']) }}" class="pb-3 border-b-2 {{ $tab === 'sales' ? 'border-red-600 text-red-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                Dispensing Sales Receipts ({{ $sales->total() }})
            </a>
        </div>

        @if($tab === 'inventory')
            <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] border-b border-slate-200">
                            <tr>
                                <th class="p-3">Medicine Name</th>
                                <th class="p-3">Generic Name</th>
                                <th class="p-3">Category</th>
                                <th class="p-3">Batch #</th>
                                <th class="p-3">Expiry Date</th>
                                <th class="p-3 text-center">Stock</th>
                                <th class="p-3 text-right">Price (KES)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($medicines as $med)
                                <tr class="hover:bg-slate-50">
                                    <td class="p-3 font-semibold text-slate-900">
                                        <div>{{ $med->name }}</div>
                                        <div class="text-[10px] text-slate-400 font-normal">{{ $med->manufacturer }}</div>
                                    </td>
                                    <td class="p-3 text-slate-500">{{ $med->generic_name ?? '—' }}</td>
                                    <td class="p-3">{{ $med->category }}</td>
                                    <td class="p-3 font-mono text-slate-400">{{ $med->batch_number ?? '—' }}</td>
                                    <td class="p-3 whitespace-nowrap">{{ $med->expiry_date ? $med->expiry_date->format('Y-m-d') : '—' }}</td>
                                    <td class="p-3 text-center">
                                        <span class="px-2 py-0.5 rounded text-[11px] font-bold {{ $med->quantity_in_stock <= $med->min_stock_level ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700' }}">
                                            {{ $med->quantity_in_stock }} {{ $med->unit_of_measure }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-right font-mono font-bold text-slate-900">{{ number_format($med->selling_price_kes, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="p-6 text-center text-slate-400">No medicines in formulary.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div>{{ $medicines->links() }}</div>
        @endif

        @if($tab === 'sales')
            <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] border-b border-slate-200">
                            <tr>
                                <th class="p-3">Receipt #</th>
                                <th class="p-3">Patient</th>
                                <th class="p-3">Date</th>
                                <th class="p-3">Dispensed By</th>
                                <th class="p-3">Payment</th>
                                <th class="p-3 text-right">Total (KES)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($sales as $sale)
                                <tr class="hover:bg-slate-50">
                                    <td class="p-3 font-mono font-semibold text-slate-900">{{ $sale->receipt_number }}</td>
                                    <td class="p-3">{{ $sale->customer_name }}</td>
                                    <td class="p-3 text-slate-400 whitespace-nowrap">{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="p-3">{{ $sale->dispensed_by }}</td>
                                    <td class="p-3 uppercase text-[10px] font-bold text-slate-600">{{ $sale->payment_method }}</td>
                                    <td class="p-3 text-right font-mono font-bold text-slate-900">{{ number_format($sale->total_amount_kes, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="p-6 text-center text-slate-400">No dispensing sales recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div>{{ $sales->links() }}</div>
        @endif

        <!-- MODAL: Add Medicine -->
        <div x-show="newMedModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" style="display: none;">
            <div @click.away="newMedModal = false" class="bg-white rounded-xl border border-slate-200 shadow-xl max-w-md w-full p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold font-heading text-slate-900">Add Medicine to Formulary</h3>
                    <button @click="newMedModal = false" class="text-slate-400">&times;</button>
                </div>
                <form action="{{ route('apps.pharmacy-manager.medicine') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Brand Name</label>
                        <input type="text" name="name" required placeholder="Augmentin 625mg" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Generic Name</label>
                        <input type="text" name="generic_name" placeholder="Amoxicillin / Clavulanate" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Category</label>
                            <input type="text" name="category" value="Antibiotics" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Batch #</label>
                            <input type="text" name="batch_number" placeholder="AUG-26A" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-mono">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Cost Price (KES)</label>
                            <input type="number" step="0.01" name="buying_price_kes" value="850" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Selling Price (KES)</label>
                            <input type="number" step="0.01" name="selling_price_kes" value="1350" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-bold">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Quantity</label>
                            <input type="number" name="quantity_in_stock" value="50" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Unit of Measure</label>
                            <input type="text" name="unit_of_measure" value="Pack of 14" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                        </div>
                    </div>
                    <input type="hidden" name="min_stock_level" value="10">
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="newMedModal = false" class="px-4 py-2 border rounded-lg text-xs text-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold">Save Medicine</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: Dispense Sale -->
        <div x-show="newSaleModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" style="display: none;">
            <div @click.away="newSaleModal = false" class="bg-white rounded-xl border border-slate-200 shadow-xl max-w-md w-full p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold font-heading text-slate-900">Dispense Medicine / Counter POS</h3>
                    <button @click="newSaleModal = false" class="text-slate-400">&times;</button>
                </div>
                <form action="{{ route('apps.pharmacy-manager.sale') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Select Medicine</label>
                        <select name="medicine_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">
                            @foreach($medicines as $m)
                                <option value="{{ $m->id }}">{{ $m->name }} (Available: {{ $m->quantity_in_stock }}) — KES {{ number_format($m->selling_price_kes, 0) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Quantity to Dispense</label>
                            <input type="number" min="1" name="quantity" value="1" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-bold">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Payment Method</label>
                            <select name="payment_method" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">
                                <option value="mpesa">M-Pesa</option>
                                <option value="cash">Cash</option>
                                <option value="insurance">Insurance / SHA</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Patient / Customer Name</label>
                        <input type="text" name="customer_name" value="Walk-in Patient" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="newSaleModal = false" class="px-4 py-2 border rounded-lg text-xs text-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold">Complete Dispense</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
