<x-layouts.app title="Property Manager">
    <div class="space-y-6" x-data="{ newPropModal: false, newTenModal: false, newPayModal: false }">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold font-heading text-slate-900 tracking-tight">Property Manager</h1>
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">Real Estate</span>
                </div>
                <p class="text-xs text-slate-500 mt-1">Estate buildings, units, tenant leasing, M-Pesa rent collections and maintenance.</p>
            </div>

            <div class="flex items-center gap-2">
                <button @click="newPropModal = true" class="px-3.5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold shadow-xs transition-colors flex items-center gap-1.5">
                    <span>+</span> Add Property
                </button>
                <button @click="newTenModal = true" class="px-3 py-2 border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-semibold transition-colors">
                    + Register Tenant
                </button>
                <button @click="newPayModal = true" class="px-3 py-2 border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-semibold transition-colors">
                    + Record Rent
                </button>
            </div>
        </div>

        <!-- Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Occupancy Rate</div>
                <div class="text-xl font-bold font-heading text-slate-900">{{ $occupiedUnits }} / {{ $totalUnits }} <span class="text-sm font-normal text-slate-500">Units Occupied</span></div>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Active Tenants</div>
                <div class="text-xl font-bold font-heading text-emerald-600">{{ $tenants->count() }} Registered</div>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Rent Collected</div>
                <div class="text-xl font-bold font-heading text-slate-900">KES {{ number_format($totalRentCollectedKes, 2) }}</div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="border-b border-slate-200 flex items-center gap-4 text-xs font-semibold">
            <a href="{{ route('apps.property-manager', ['tab' => 'properties']) }}" class="pb-3 border-b-2 {{ $tab === 'properties' ? 'border-red-600 text-red-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                Properties & Estates ({{ $properties->count() }})
            </a>
            <a href="{{ route('apps.property-manager', ['tab' => 'tenants']) }}" class="pb-3 border-b-2 {{ $tab === 'tenants' ? 'border-red-600 text-red-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                Active Tenants ({{ $tenants->count() }})
            </a>
            <a href="{{ route('apps.property-manager', ['tab' => 'payments']) }}" class="pb-3 border-b-2 {{ $tab === 'payments' ? 'border-red-600 text-red-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                Rent Payments ({{ $payments->total() }})
            </a>
        </div>

        @if($tab === 'properties')
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse($properties as $prop)
                    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <h3 class="font-bold text-slate-900 text-sm font-heading">{{ $prop->name }}</h3>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">{{ $prop->property_type }}</span>
                            </div>
                            <p class="text-xs text-slate-500 mb-3">{{ $prop->location }}, {{ $prop->county }}</p>
                            <div class="space-y-1 text-xs text-slate-600 mb-4">
                                <div>Total Units: <strong class="text-slate-900 font-semibold">{{ $prop->total_units }}</strong></div>
                                <div>Caretaker: <span class="text-slate-700">{{ $prop->caretaker_name ?? '—' }} ({{ $prop->caretaker_phone ?? '' }})</span></div>
                            </div>
                        </div>
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                            <span>Units generated</span>
                            <span class="font-semibold text-slate-700">{{ $prop->units->count() }} Units</span>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full p-8 text-center bg-white border border-slate-200 rounded-xl text-slate-400 text-xs">
                        No property estates added yet. Click "+ Add Property" to register one.
                    </div>
                @endforelse
            </div>
        @endif

        @if($tab === 'tenants')
            <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] border-b border-slate-200">
                            <tr>
                                <th class="p-3">Tenant Name</th>
                                <th class="p-3">Contact</th>
                                <th class="p-3">Property</th>
                                <th class="p-3">National ID</th>
                                <th class="p-3">Move In Date</th>
                                <th class="p-3 text-right">Monthly Rent (KES)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($tenants as $ten)
                                <tr class="hover:bg-slate-50">
                                    <td class="p-3 font-semibold text-slate-900">{{ $ten->name }}</td>
                                    <td class="p-3">
                                        <div>{{ $ten->phone }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $ten->email }}</div>
                                    </td>
                                    <td class="p-3">{{ $ten->property->name ?? '—' }}</td>
                                    <td class="p-3 font-mono text-slate-500">{{ $ten->national_id ?? '—' }}</td>
                                    <td class="p-3 whitespace-nowrap">{{ $ten->move_in_date ?? '—' }}</td>
                                    <td class="p-3 text-right font-mono font-bold text-slate-900">{{ number_format($ten->monthly_rent_kes, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="p-6 text-center text-slate-400">No tenants registered yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($tab === 'payments')
            <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] border-b border-slate-200">
                            <tr>
                                <th class="p-3">Receipt #</th>
                                <th class="p-3">Month For</th>
                                <th class="p-3">Payment Date</th>
                                <th class="p-3">Method</th>
                                <th class="p-3">Ref Code</th>
                                <th class="p-3 text-right">Amount Paid (KES)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($payments as $pay)
                                <tr class="hover:bg-slate-50">
                                    <td class="p-3 font-semibold text-slate-900">{{ $pay->receipt_number }}</td>
                                    <td class="p-3 font-medium text-slate-900">{{ $pay->month_for }}</td>
                                    <td class="p-3 whitespace-nowrap">{{ $pay->payment_date->format('Y-m-d') }}</td>
                                    <td class="p-3">{{ $pay->payment_method }}</td>
                                    <td class="p-3 font-mono text-slate-400">{{ $pay->transaction_reference ?? '—' }}</td>
                                    <td class="p-3 text-right font-mono font-bold text-slate-900">{{ number_format($pay->amount_kes, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="p-6 text-center text-slate-400">No rent payments recorded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div>{{ $payments->links() }}</div>
        @endif

        <!-- MODAL: Add Property -->
        <div x-show="newPropModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" style="display: none;">
            <div @click.away="newPropModal = false" class="bg-white rounded-xl border border-slate-200 shadow-xl max-w-md w-full p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold font-heading text-slate-900">Register Estate Property</h3>
                    <button @click="newPropModal = false" class="text-slate-400">&times;</button>
                </div>
                <form action="{{ route('apps.property-manager.property') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Property / Building Name</label>
                        <input type="text" name="name" required placeholder="e.g. Riverside Gardens" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Property Type</label>
                            <select name="property_type" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">
                                <option value="apartment_building">Apartment Building</option>
                                <option value="commercial">Commercial Complex</option>
                                <option value="gated_villas">Gated Villas</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Total Units</label>
                            <input type="number" name="total_units" value="12" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Location / Road</label>
                        <input type="text" name="location" required placeholder="Riverside Drive, Westlands" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Caretaker Name</label>
                            <input type="text" name="caretaker_name" placeholder="John Ochieng" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Caretaker Phone</label>
                            <input type="text" name="caretaker_phone" placeholder="+254 712 000 000" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="newPropModal = false" class="px-4 py-2 border rounded-lg text-xs text-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold">Save Property</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: Register Tenant -->
        <div x-show="newTenModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" style="display: none;">
            <div @click.away="newTenModal = false" class="bg-white rounded-xl border border-slate-200 shadow-xl max-w-md w-full p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold font-heading text-slate-900">Register Tenant</h3>
                    <button @click="newTenModal = false" class="text-slate-400">&times;</button>
                </div>
                <form action="{{ route('apps.property-manager.tenant') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Assign to Property</label>
                        <select name="property_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">
                            @foreach($properties as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Tenant Full Name</label>
                        <input type="text" name="name" required placeholder="Dr. Sarah Ndwiga" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Phone</label>
                            <input type="text" name="phone" required placeholder="+254 722 000 000" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">National ID</label>
                            <input type="text" name="national_id" placeholder="28391823" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Monthly Rent (KES)</label>
                            <input type="number" name="monthly_rent_kes" value="55000" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-bold">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Move In Date</label>
                            <input type="date" name="move_in_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="newTenModal = false" class="px-4 py-2 border rounded-lg text-xs text-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold">Register Tenant</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: Record Rent Payment -->
        <div x-show="newPayModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" style="display: none;">
            <div @click.away="newPayModal = false" class="bg-white rounded-xl border border-slate-200 shadow-xl max-w-md w-full p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold font-heading text-slate-900">Record Rent Receipt</h3>
                    <button @click="newPayModal = false" class="text-slate-400">&times;</button>
                </div>
                <form action="{{ route('apps.property-manager.payment') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Select Tenant</label>
                        <select name="tenant_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">
                            @foreach($tenants as $t)
                                <option value="{{ $t->id }}">{{ $t->name }} (Rent: KES {{ number_format($t->monthly_rent_kes, 0) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Amount Paid (KES)</label>
                            <input type="number" name="amount_kes" value="55000" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-bold">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Month Period</label>
                            <input type="text" name="month_for" value="{{ date('F Y') }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Payment Method</label>
                            <select name="payment_method" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">
                                <option value="M-Pesa Paybill">M-Pesa Paybill</option>
                                <option value="Bank EFT">Bank EFT</option>
                                <option value="Cash Deposit">Cash Deposit</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">M-Pesa / Bank Ref</label>
                            <input type="text" name="transaction_reference" placeholder="QHK98102KL" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-mono">
                        </div>
                    </div>
                    <input type="hidden" name="payment_date" value="{{ date('Y-m-d') }}">
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="newPayModal = false" class="px-4 py-2 border rounded-lg text-xs text-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold">Generate Receipt</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
