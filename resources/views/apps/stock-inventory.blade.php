<x-layouts.app title="Stock & Inventory">
    <div class="space-y-6" x-data="{ newProdModal: false, adjustModal: false, adjustProductId: null, adjustProductName: '' }">
        <!-- App Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold font-heading text-slate-900 tracking-tight">Stock & Inventory</h1>
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-800 border border-amber-200">Warehouse & Stock</span>
                </div>
                <p class="text-xs text-slate-500 mt-1">Multi-location warehouse stock, SKU tracking, reorder alerts, and in/out stock adjustments.</p>
            </div>

            <div class="flex items-center gap-2">
                <button @click="newProdModal = true" class="px-3.5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold shadow-xs transition-colors flex items-center gap-1.5">
                    <span>+</span> Add Stock Product
                </button>
            </div>
        </div>

        <!-- Metric Badges -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Catalog Items</div>
                <div class="text-xl font-bold font-heading text-slate-900">{{ number_format($totalItems) }} Products</div>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Warehouse Units</div>
                <div class="text-xl font-bold font-heading text-slate-900">{{ number_format($totalUnits) }} Units</div>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Low Stock Warning</div>
                <div class="text-xl font-bold font-heading text-amber-600">{{ $lowStockCount }} Reorder Triggers</div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white border border-slate-200 rounded-xl p-3 shadow-xs flex flex-wrap items-center justify-between gap-3 text-xs">
            <form action="{{ route('apps.stock-inventory') }}" method="GET" class="flex flex-wrap items-center gap-2 flex-1">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search SKU, barcode or product title..." class="px-3 py-1.5 border border-slate-300 rounded-lg text-xs w-72 focus:ring-2 focus:ring-red-500 focus:outline-none">
                <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg">Search</button>
            </form>
        </div>

        <!-- Inventory Table -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] border-b border-slate-200">
                        <tr>
                            <th class="p-3">Product Name</th>
                            <th class="p-3">SKU / Barcode</th>
                            <th class="p-3">Category</th>
                            <th class="p-3">Location</th>
                            <th class="p-3 text-center">In Stock</th>
                            <th class="p-3 text-right">Cost (KES)</th>
                            <th class="p-3 text-right">Selling Price (KES)</th>
                            <th class="p-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($products as $p)
                            <tr class="hover:bg-slate-50">
                                <td class="p-3 font-semibold text-slate-900">
                                    <div>{{ $p->name }}</div>
                                    <div class="text-[10px] text-slate-400 font-normal">{{ $p->supplier_name }}</div>
                                </td>
                                <td class="p-3 font-mono text-slate-500">
                                    <div>{{ $p->sku ?? '—' }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $p->barcode ?? '' }}</div>
                                </td>
                                <td class="p-3">{{ $p->category }}</td>
                                <td class="p-3 text-slate-500">{{ $p->location ?? 'Main Warehouse' }}</td>
                                <td class="p-3 text-center">
                                    <span class="px-2.5 py-1 rounded-md text-[11px] font-bold {{ $p->current_quantity <= $p->min_stock_level ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-slate-100 text-slate-800' }}">
                                        {{ $p->current_quantity }} {{ $p->unit }}
                                    </span>
                                </td>
                                <td class="p-3 text-right font-mono">{{ number_format($p->buying_price_kes, 2) }}</td>
                                <td class="p-3 text-right font-mono font-bold text-slate-900">{{ number_format($p->selling_price_kes, 2) }}</td>
                                <td class="p-3 text-right">
                                    <button @click="adjustProductId = {{ $p->id }}; adjustProductName = '{{ addslashes($p->name) }}'; adjustModal = true" class="px-2.5 py-1 text-slate-700 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded font-semibold text-[11px] transition-colors">
                                        Adjust Stock
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center text-slate-400">No stock products found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            {{ $products->links() }}
        </div>

        <!-- MODAL: Add Stock Product -->
        <div x-show="newProdModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" style="display: none;">
            <div @click.away="newProdModal = false" class="bg-white rounded-xl border border-slate-200 shadow-xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold font-heading text-slate-900">Add Warehouse Inventory Product</h3>
                    <button @click="newProdModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>
                <form action="{{ route('apps.stock-inventory.product') }}" method="POST" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Product Name</label>
                        <input type="text" name="name" required placeholder="e.g. Matte Finish Business Cards (Pack 100)" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">SKU</label>
                            <input type="text" name="sku" placeholder="PRD-CRD-01" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-mono">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Barcode</label>
                            <input type="text" name="barcode" placeholder="61611009871" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Category</label>
                            <input type="text" name="category" value="Printing Materials" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Warehouse Location</label>
                            <input type="text" name="location" placeholder="Aisle 3 - Bay B" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Current Stock</label>
                            <input type="number" name="current_quantity" value="100" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-bold">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Min Reorder Level</label>
                            <input type="number" name="min_stock_level" value="20" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Unit</label>
                            <input type="text" name="unit" value="Packs" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Buying Cost (KES)</label>
                            <input type="number" step="0.01" name="buying_price_kes" value="800" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Selling Price (KES)</label>
                            <input type="number" step="0.01" name="selling_price_kes" value="1500" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-bold">
                        </div>
                    </div>

                    <input type="hidden" name="max_stock_level" value="1000">

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="newProdModal = false" class="px-4 py-2 border border-slate-300 rounded-lg text-xs text-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold">Save to Inventory</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: Adjust Stock -->
        <div x-show="adjustModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" style="display: none;">
            <div @click.away="adjustModal = false" class="bg-white rounded-xl border border-slate-200 shadow-xl max-w-md w-full p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold font-heading text-slate-900">Adjust Stock Quantity</h3>
                    <button @click="adjustModal = false" class="text-slate-400">&times;</button>
                </div>
                <form :action="'/apps/stock-inventory/' + adjustProductId + '/adjust'" method="POST" class="space-y-3.5">
                    @csrf
                    <div>
                        <div class="text-xs text-slate-500 mb-1">Product:</div>
                        <div class="text-sm font-bold text-slate-900" x-text="adjustProductName"></div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Stock Adjustment (+ or -)</label>
                        <input type="number" name="quantity_change" required placeholder="e.g. 50 or -10" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-bold font-mono">
                        <p class="text-[10px] text-slate-400 mt-1">Positive number for received shipment, negative number for damaged/dispatched goods.</p>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Reason for Adjustment</label>
                        <input type="text" name="reason" required placeholder="e.g. Supplier delivery PO-4482 or internal breakage" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="adjustModal = false" class="px-4 py-2 border rounded-lg text-xs text-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold">Apply Stock Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
