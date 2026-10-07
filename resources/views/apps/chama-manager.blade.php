<x-layouts.app title="Chama & Sacco Manager">
    <div class="space-y-6" x-data="{ newMemModal: false, newContribModal: false }">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold font-heading text-slate-900 tracking-tight">Chama & Sacco Manager</h1>
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-purple-100 text-purple-800 border border-purple-200">Community Finance</span>
                </div>
                <p class="text-xs text-slate-500 mt-1">Group savings, merry-go-round monthly contributions, micro-loans and welfare funds.</p>
            </div>

            <div class="flex items-center gap-2">
                <button @click="newContribModal = true" class="px-3.5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold shadow-xs transition-colors flex items-center gap-1.5">
                    <span>+</span> Record Contribution
                </button>
                <button @click="newMemModal = true" class="px-3 py-2 border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-semibold transition-colors">
                    + Register Member
                </button>
            </div>
        </div>

        <!-- Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Members Enrolled</div>
                <div class="text-xl font-bold font-heading text-slate-900">{{ $totalMembers }} Active Members</div>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Group Savings</div>
                <div class="text-xl font-bold font-heading text-purple-700">KES {{ number_format($totalContributionsKes, 2) }}</div>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Active Loans Disbursed</div>
                <div class="text-xl font-bold font-heading text-slate-900">KES {{ number_format($totalActiveLoansKes, 2) }}</div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="border-b border-slate-200 flex items-center gap-4 text-xs font-semibold">
            <a href="{{ route('apps.chama-manager', ['tab' => 'members']) }}" class="pb-3 border-b-2 {{ $tab === 'members' ? 'border-red-600 text-red-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                Chama Members ({{ $members->total() }})
            </a>
            <a href="{{ route('apps.chama-manager', ['tab' => 'contributions']) }}" class="pb-3 border-b-2 {{ $tab === 'contributions' ? 'border-red-600 text-red-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                Monthly Contributions ({{ $contributions->total() }})
            </a>
            <a href="{{ route('apps.chama-manager', ['tab' => 'loans']) }}" class="pb-3 border-b-2 {{ $tab === 'loans' ? 'border-red-600 text-red-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                Loans & Micro-Credit ({{ $loans->total() }})
            </a>
        </div>

        @if($tab === 'members')
            <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] border-b border-slate-200">
                            <tr>
                                <th class="p-3">Member ID</th>
                                <th class="p-3">Full Name</th>
                                <th class="p-3">Role</th>
                                <th class="p-3">Phone</th>
                                <th class="p-3 text-right">Total Savings (KES)</th>
                                <th class="p-3 text-right">Loan Balance (KES)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($members as $m)
                                <tr class="hover:bg-slate-50">
                                    <td class="p-3 font-mono font-semibold text-slate-900">{{ $m->membership_number }}</td>
                                    <td class="p-3 font-semibold text-slate-900">{{ $m->name }}</td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-purple-50 text-purple-700">
                                            {{ $m->role_in_chama }}
                                        </span>
                                    </td>
                                    <td class="p-3">{{ $m->phone }}</td>
                                    <td class="p-3 text-right font-mono font-bold text-slate-900">{{ number_format($m->total_contributions_kes, 2) }}</td>
                                    <td class="p-3 text-right font-mono text-slate-500">{{ number_format($m->outstanding_loan_balance_kes, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="p-6 text-center text-slate-400">No members registered yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div>{{ $members->links() }}</div>
        @endif

        @if($tab === 'contributions')
            <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] border-b border-slate-200">
                            <tr>
                                <th class="p-3">Receipt #</th>
                                <th class="p-3">Member</th>
                                <th class="p-3">Period</th>
                                <th class="p-3">Date</th>
                                <th class="p-3">Method</th>
                                <th class="p-3 text-right">Total Contributed (KES)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($contributions as $c)
                                <tr class="hover:bg-slate-50">
                                    <td class="p-3 font-mono font-semibold text-slate-900">{{ $c->receipt_number }}</td>
                                    <td class="p-3 font-medium text-slate-900">{{ $c->member->name ?? 'Member' }}</td>
                                    <td class="p-3">{{ $c->month_period }}</td>
                                    <td class="p-3 whitespace-nowrap">{{ $c->payment_date->format('Y-m-d') }}</td>
                                    <td class="p-3">{{ $c->payment_method }}</td>
                                    <td class="p-3 text-right font-mono font-bold text-slate-900">{{ number_format($c->total_amount_kes, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="p-6 text-center text-slate-400">No contributions recorded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div>{{ $contributions->links() }}</div>
        @endif

        @if($tab === 'loans')
            <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] border-b border-slate-200">
                            <tr>
                                <th class="p-3">Member</th>
                                <th class="p-3 text-right">Principal</th>
                                <th class="p-3 text-right">Total Payable</th>
                                <th class="p-3 text-right">Repaid</th>
                                <th class="p-3">Due Date</th>
                                <th class="p-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($loans as $loan)
                                <tr class="hover:bg-slate-50">
                                    <td class="p-3 font-semibold text-slate-900">{{ $loan->member->name ?? 'Member' }}</td>
                                    <td class="p-3 text-right font-mono">{{ number_format($loan->principal_kes, 2) }}</td>
                                    <td class="p-3 text-right font-mono font-bold text-slate-900">{{ number_format($loan->total_payable_kes, 2) }}</td>
                                    <td class="p-3 text-right font-mono text-emerald-600">{{ number_format($loan->amount_repaid_kes, 2) }}</td>
                                    <td class="p-3 whitespace-nowrap">{{ $loan->due_date }}</td>
                                    <td class="p-3 text-center">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $loan->status === 'cleared' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                            {{ $loan->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="p-6 text-center text-slate-400">No loans active.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- MODAL: Add Member -->
        <div x-show="newMemModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" style="display: none;">
            <div @click.away="newMemModal = false" class="bg-white rounded-xl border border-slate-200 shadow-xl max-w-md w-full p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold font-heading text-slate-900">Register Chama Member</h3>
                    <button @click="newMemModal = false" class="text-slate-400">&times;</button>
                </div>
                <form action="{{ route('apps.chama-manager.member') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Full Name</label>
                        <input type="text" name="name" required placeholder="Mary Wambui" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Phone</label>
                            <input type="text" name="phone" required placeholder="+254 722 000 000" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">National ID</label>
                            <input type="text" name="national_id" placeholder="24190812" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-mono">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Role in Group</label>
                        <select name="role_in_chama" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">
                            <option value="Member">Member</option>
                            <option value="Chairperson">Chairperson</option>
                            <option value="Treasurer">Treasurer</option>
                            <option value="Secretary">Secretary</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="newMemModal = false" class="px-4 py-2 border rounded-lg text-xs text-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold">Enroll Member</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: Record Contribution -->
        <div x-show="newContribModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" style="display: none;">
            <div @click.away="newContribModal = false" class="bg-white rounded-xl border border-slate-200 shadow-xl max-w-md w-full p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold font-heading text-slate-900">Record Member Contribution</h3>
                    <button @click="newContribModal = false" class="text-slate-400">&times;</button>
                </div>
                <form action="{{ route('apps.chama-manager.contribution') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Member</label>
                        <select name="member_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">
                            @foreach($members as $m)
                                <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->membership_number }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Monthly Savings (KES)</label>
                            <input type="number" name="savings_amount_kes" value="5000" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-bold">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Welfare Fund (KES)</label>
                            <input type="number" name="welfare_amount_kes" value="500" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Month Period</label>
                            <input type="text" name="month_period" value="{{ date('F Y') }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Payment Method</label>
                            <select name="payment_method" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">
                                <option value="M-Pesa">M-Pesa</option>
                                <option value="Bank Deposit">Bank Deposit</option>
                                <option value="Cash">Cash</option>
                            </select>
                        </div>
                    </div>
                    <input type="hidden" name="payment_date" value="{{ date('Y-m-d') }}">
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="newContribModal = false" class="px-4 py-2 border rounded-lg text-xs text-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold">Record Receipt</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
