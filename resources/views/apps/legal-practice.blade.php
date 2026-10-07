<x-layouts.app title="Legal Practice Manager">
    <div class="space-y-6" x-data="{ newMatterModal: false }">
        <!-- App Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold font-heading text-slate-900 tracking-tight">Legal Practice Manager</h1>
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-100 text-indigo-800 border border-indigo-200">Advocate Case Management</span>
                </div>
                <p class="text-xs text-slate-500 mt-1">Court diaries, legal matters, client retainers, billable disbursements and cause lists.</p>
            </div>

            <div class="flex items-center gap-2">
                <button @click="newMatterModal = true" class="px-3.5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold shadow-xs transition-colors flex items-center gap-1.5">
                    <span>+</span> File New Matter
                </button>
            </div>
        </div>

        <!-- Metric Badges -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Active Legal Matters</div>
                <div class="text-xl font-bold font-heading text-slate-900">{{ number_format($activeMattersCount) }} Open Cases</div>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Matters Filed</div>
                <div class="text-xl font-bold font-heading text-indigo-600">{{ number_format($totalMatters) }} Total Files</div>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Billed Legal Fees</div>
                <div class="text-xl font-bold font-heading text-slate-900">KES {{ number_format($totalBilledKes, 2) }}</div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white border border-slate-200 rounded-xl p-3 shadow-xs flex flex-wrap items-center justify-between gap-3 text-xs">
            <form action="{{ route('apps.legal-practice') }}" method="GET" class="flex flex-wrap items-center gap-2 flex-1">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search matter #, title or client name..." class="px-3 py-1.5 border border-slate-300 rounded-lg text-xs w-72 focus:ring-2 focus:ring-red-500 focus:outline-none">
                <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg">Search</button>
            </form>
        </div>

        <!-- Matters Table -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] border-b border-slate-200">
                        <tr>
                            <th class="p-3">Matter #</th>
                            <th class="p-3">Title & Client</th>
                            <th class="p-3">Practice Area</th>
                            <th class="p-3">Court / Forum</th>
                            <th class="p-3">Advocate</th>
                            <th class="p-3">Next Court Date</th>
                            <th class="p-3 text-right">Billed (KES)</th>
                            <th class="p-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($matters as $m)
                            <tr class="hover:bg-slate-50">
                                <td class="p-3 font-mono font-bold text-slate-900 whitespace-nowrap">{{ $m->matter_number }}</td>
                                <td class="p-3">
                                    <div class="font-semibold text-slate-900">{{ $m->title }}</div>
                                    <div class="text-[10px] text-slate-400">Client: {{ $m->client_name }}</div>
                                </td>
                                <td class="p-3">{{ $m->practice_area }}</td>
                                <td class="p-3 text-slate-500">{{ $m->court_forum ?? 'Arbitration / Out of Court' }}</td>
                                <td class="p-3 whitespace-nowrap">{{ $m->assigned_advocate }}</td>
                                <td class="p-3 whitespace-nowrap font-mono text-[11px] {{ $m->next_court_date ? 'text-red-600 font-bold' : 'text-slate-400' }}">
                                    {{ $m->next_court_date ? $m->next_court_date->format('Y-m-d') : '—' }}
                                </td>
                                <td class="p-3 text-right font-mono font-bold text-slate-900">{{ number_format($m->total_billed_kes, 2) }}</td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $m->status === 'open' ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $m->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center text-slate-400">No legal matters filed yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            {{ $matters->links() }}
        </div>

        <!-- MODAL: File Legal Matter -->
        <div x-show="newMatterModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" style="display: none;">
            <div @click.away="newMatterModal = false" class="bg-white rounded-xl border border-slate-200 shadow-xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold font-heading text-slate-900">File New Legal Matter</h3>
                    <button @click="newMatterModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>
                <form action="{{ route('apps.legal-practice.store') }}" method="POST" class="space-y-3.5">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Matter Reference #</label>
                            <input type="text" name="matter_number" value="MAT-{{ date('Y') }}-{{ rand(100, 999) }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-mono font-bold focus:ring-2 focus:ring-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Practice Area</label>
                            <select name="practice_area" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">
                                <option value="Commercial Litigation">Commercial Litigation</option>
                                <option value="Corporate & M&A">Corporate & M&A</option>
                                <option value="Conveyancing & Land">Conveyancing & Land</option>
                                <option value="Employment & Labour">Employment & Labour</option>
                                <option value="IP & Trademark">IP & Trademark</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Matter / Suit Title</label>
                        <input type="text" name="title" required placeholder="e.g. Acme Ltd vs. Beta Suppliers (Commercial Debt Claim)" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Client Name</label>
                            <input type="text" name="client_name" required placeholder="Acme Africa Ltd" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Assigned Advocate</label>
                            <input type="text" name="assigned_advocate" value="Lead Advocate" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Court / Forum</label>
                            <input type="text" name="court_forum" placeholder="Milimani Commercial Court - Court 4" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Next Court Date</label>
                            <input type="date" name="next_court_date" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Retainer / Total Fee Billed (KES)</label>
                        <input type="number" step="0.01" name="total_billed_kes" value="150000" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-bold font-mono">
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="newMatterModal = false" class="px-4 py-2 border border-slate-300 rounded-lg text-xs text-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold">File Matter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
