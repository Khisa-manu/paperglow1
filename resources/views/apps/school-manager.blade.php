<x-layouts.app title="School & Academy Manager">
    <div class="space-y-6" x-data="{ newStudentModal: false }">
        <!-- App Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold font-heading text-slate-900 tracking-tight">School & Academy Manager</h1>
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-yellow-100 text-yellow-800 border border-yellow-200">Education ERP</span>
                </div>
                <p class="text-xs text-slate-500 mt-1">Student enrollment, CBC grade rosters, parent contacts and school fee ledger tracking.</p>
            </div>

            <div class="flex items-center gap-2">
                <button @click="newStudentModal = true" class="px-3.5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold shadow-xs transition-colors flex items-center gap-1.5">
                    <span>+</span> Enroll New Student
                </button>
            </div>
        </div>

        <!-- Metric Badges -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Enrolled Students</div>
                <div class="text-xl font-bold font-heading text-slate-900">{{ number_format($totalStudents) }} Students</div>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Outstanding Term Fee Arrears</div>
                <div class="text-xl font-bold font-heading text-red-600">KES {{ number_format($totalFeeBalanceKes, 2) }}</div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white border border-slate-200 rounded-xl p-3 shadow-xs flex flex-wrap items-center justify-between gap-3 text-xs">
            <form action="{{ route('apps.school-manager') }}" method="GET" class="flex flex-wrap items-center gap-2 flex-1">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search admission #, student name or parent..." class="px-3 py-1.5 border border-slate-300 rounded-lg text-xs w-72 focus:ring-2 focus:ring-red-500 focus:outline-none">
                <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg">Search</button>
            </form>
        </div>

        <!-- Students Table -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] border-b border-slate-200">
                        <tr>
                            <th class="p-3">Admission #</th>
                            <th class="p-3">Student Name</th>
                            <th class="p-3">Grade / Class</th>
                            <th class="p-3">Parent / Guardian</th>
                            <th class="p-3">Contact</th>
                            <th class="p-3 text-right">Fee Arrears (KES)</th>
                            <th class="p-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($students as $s)
                            <tr class="hover:bg-slate-50">
                                <td class="p-3 font-mono font-bold text-slate-900 whitespace-nowrap">{{ $s->admission_number }}</td>
                                <td class="p-3 font-semibold text-slate-900">{{ $s->full_name }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-yellow-50 text-yellow-800">
                                        {{ $s->grade_class }}
                                    </span>
                                </td>
                                <td class="p-3 font-medium text-slate-900">{{ $s->guardian_name }}</td>
                                <td class="p-3 text-slate-500">{{ $s->guardian_phone }}</td>
                                <td class="p-3 text-right font-mono font-bold {{ $s->fee_balance_kes > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                    {{ number_format($s->fee_balance_kes, 2) }}
                                </td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700">
                                        {{ $s->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-400">No students enrolled yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            {{ $students->links() }}
        </div>

        <!-- MODAL: Enroll Student -->
        <div x-show="newStudentModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" style="display: none;">
            <div @click.away="newStudentModal = false" class="bg-white rounded-xl border border-slate-200 shadow-xl max-w-md w-full p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold font-heading text-slate-900">Enroll New Student</h3>
                    <button @click="newStudentModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>
                <form action="{{ route('apps.school-manager.student') }}" method="POST" class="space-y-3.5">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Admission #</label>
                            <input type="text" name="admission_number" value="ADM-{{ rand(1000, 9999) }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-mono font-bold focus:ring-2 focus:ring-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Class / Grade</label>
                            <select name="grade_class" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">
                                <option value="Grade 1 (CBC)">Grade 1 (CBC)</option>
                                <option value="Grade 2 (CBC)">Grade 2 (CBC)</option>
                                <option value="Grade 3 (CBC)">Grade 3 (CBC)</option>
                                <option value="Grade 4 (CBC)">Grade 4 (CBC)</option>
                                <option value="Grade 5 (CBC)">Grade 5 (CBC)</option>
                                <option value="Grade 6 (CBC)">Grade 6 (CBC)</option>
                                <option value="Junior Secondary 7">Junior Secondary 7</option>
                                <option value="Junior Secondary 8">Junior Secondary 8</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Student Full Name</label>
                        <input type="text" name="full_name" required placeholder="Brian Omondi" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Parent / Guardian Name</label>
                            <input type="text" name="guardian_name" required placeholder="George Omondi" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Parent Phone</label>
                            <input type="text" name="guardian_phone" required placeholder="+254 722 000 000" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Term Fee Balance (KES)</label>
                        <input type="number" step="0.01" name="fee_balance_kes" value="0.00" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-bold font-mono">
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="newStudentModal = false" class="px-4 py-2 border border-slate-300 rounded-lg text-xs text-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold">Enroll Student</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
