<x-layouts.app title="Clinic & OPD Manager">
    <div class="space-y-6" x-data="{ newPatModal: false, newConsultModal: false, activeTab: 'patients' }">
        <!-- App Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold font-heading text-slate-900 tracking-tight">Clinic & OPD Manager</h1>
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-100 text-blue-800 border border-blue-200">Outpatient Healthcare</span>
                </div>
                <p class="text-xs text-slate-500 mt-1">Patient medical records, clinical notes, doctor diagnoses, and outpatient consultation logs.</p>
            </div>

            <div class="flex items-center gap-2">
                <button @click="newConsultModal = true" class="px-3.5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold shadow-xs transition-colors flex items-center gap-1.5">
                    <span>+</span> Record Consultation
                </button>
                <button @click="newPatModal = true" class="px-3 py-2 border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-semibold transition-colors">
                    + Register Patient
                </button>
            </div>
        </div>

        <!-- Metric Badges -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Registered Patients</div>
                <div class="text-xl font-bold font-heading text-slate-900">{{ number_format($totalPatients) }} Patients</div>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Clinical Consultations Completed</div>
                <div class="text-xl font-bold font-heading text-blue-600">{{ number_format($totalConsultations) }} Sessions</div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="border-b border-slate-200 flex items-center gap-4 text-xs font-semibold">
            <button @click="activeTab = 'patients'" :class="activeTab === 'patients' ? 'border-red-600 text-red-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800'" class="pb-3 border-b-2">
                Patient Medical Directory ({{ $patients->total() }})
            </button>
            <button @click="activeTab = 'consultations'" :class="activeTab === 'consultations' ? 'border-red-600 text-red-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800'" class="pb-3 border-b-2">
                Clinical Consultations ({{ $consultations->total() }})
            </button>
        </div>

        <!-- TAB 1: Patients -->
        <div x-show="activeTab === 'patients'">
            <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] border-b border-slate-200">
                            <tr>
                                <th class="p-3">Patient #</th>
                                <th class="p-3">Full Name</th>
                                <th class="p-3">Gender</th>
                                <th class="p-3">Phone</th>
                                <th class="p-3">National ID</th>
                                <th class="p-3">Known Allergies</th>
                                <th class="p-3">Registered</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($patients as $p)
                                <tr class="hover:bg-slate-50">
                                    <td class="p-3 font-mono font-bold text-slate-900 whitespace-nowrap">{{ $p->patient_number }}</td>
                                    <td class="p-3 font-semibold text-slate-900">{{ $p->full_name }}</td>
                                    <td class="p-3">{{ $p->gender }}</td>
                                    <td class="p-3">{{ $p->phone ?? '—' }}</td>
                                    <td class="p-3 font-mono text-slate-500">{{ $p->national_id ?? '—' }}</td>
                                    <td class="p-3 text-slate-500">{{ $p->allergies ?? 'No known allergies' }}</td>
                                    <td class="p-3 whitespace-nowrap text-slate-400 text-[10px]">{{ $p->created_at->format('Y-m-d') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-slate-400">No patients registered yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-4">
                {{ $patients->links() }}
            </div>
        </div>

        <!-- TAB 2: Consultations -->
        <div x-show="activeTab === 'consultations'" style="display: none;">
            <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] border-b border-slate-200">
                            <tr>
                                <th class="p-3">Patient</th>
                                <th class="p-3">Doctor</th>
                                <th class="p-3">Symptoms</th>
                                <th class="p-3">Diagnosis</th>
                                <th class="p-3">Prescription</th>
                                <th class="p-3 text-right">Fee (KES)</th>
                                <th class="p-3">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($consultations as $c)
                                <tr class="hover:bg-slate-50">
                                    <td class="p-3">
                                        <div class="font-semibold text-slate-900">{{ $c->patient->full_name ?? 'Patient' }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $c->patient->patient_number ?? '' }}</div>
                                    </td>
                                    <td class="p-3 font-medium text-slate-900 whitespace-nowrap">{{ $c->doctor_name }}</td>
                                    <td class="p-3 text-slate-600 max-w-xs truncate">{{ $c->symptoms ?? 'Routine checkup' }}</td>
                                    <td class="p-3 font-medium text-slate-900">{{ $c->diagnosis }}</td>
                                    <td class="p-3 text-slate-600 max-w-xs truncate">{{ $c->prescription ?? 'None' }}</td>
                                    <td class="p-3 text-right font-mono font-bold text-slate-900">{{ number_format($c->consultation_fee_kes, 2) }}</td>
                                    <td class="p-3 whitespace-nowrap text-slate-400 text-[10px]">{{ $c->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-slate-400">No clinical consultations recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-4">
                {{ $consultations->links() }}
            </div>
        </div>

        <!-- MODAL: Register Patient -->
        <div x-show="newPatModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" style="display: none;">
            <div @click.away="newPatModal = false" class="bg-white rounded-xl border border-slate-200 shadow-xl max-w-md w-full p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold font-heading text-slate-900">Register Outpatient</h3>
                    <button @click="newPatModal = false" class="text-slate-400">&times;</button>
                </div>
                <form action="{{ route('apps.clinic-manager.patient') }}" method="POST" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Full Name</label>
                        <input type="text" name="full_name" required placeholder="Mercy Njeri" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Gender</label>
                            <select name="gender" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">
                                <option value="Female">Female</option>
                                <option value="Male">Male</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Phone</label>
                            <input type="text" name="phone" placeholder="+254 711 000 000" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Known Allergies / Chronic Conditions</label>
                        <input type="text" name="allergies" placeholder="Penicillin, Sulfa drugs" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="newPatModal = false" class="px-4 py-2 border rounded-lg text-xs text-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold">Register Patient</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: Record Consultation -->
        <div x-show="newConsultModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" style="display: none;">
            <div @click.away="newConsultModal = false" class="bg-white rounded-xl border border-slate-200 shadow-xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold font-heading text-slate-900">Record Doctor Consultation</h3>
                    <button @click="newConsultModal = false" class="text-slate-400">&times;</button>
                </div>
                <form action="{{ route('apps.clinic-manager.consultation') }}" method="POST" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Select Patient</label>
                        <select name="patient_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">
                            @foreach($patients as $p)
                                <option value="{{ $p->id }}">{{ $p->full_name }} ({{ $p->patient_number }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Attending Clinician</label>
                            <input type="text" name="doctor_name" value="Dr. Ochieng" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Consultation Fee (KES)</label>
                            <input type="number" step="0.01" name="consultation_fee_kes" value="1500" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-bold">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Presented Symptoms</label>
                        <input type="text" name="symptoms" placeholder="Persistent dry cough, mild fever 3 days" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Clinical Diagnosis</label>
                        <input type="text" name="diagnosis" required placeholder="Upper Respiratory Tract Infection (URTI)" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Prescription / Treatment Plan</label>
                        <textarea name="prescription" rows="2" placeholder="Augmentin 625mg tabs BD x 7 days, Paracetamol 1g TDS x 3 days" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="newConsultModal = false" class="px-4 py-2 border rounded-lg text-xs text-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold">Save Consultation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
