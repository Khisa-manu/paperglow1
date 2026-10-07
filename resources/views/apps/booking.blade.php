<x-layouts.app title="Booking & Appointments">
    <div class="space-y-6" x-data="{ newBookingModal: false }">
        <!-- App Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold font-heading text-slate-900 tracking-tight">Booking & Appointments</h1>
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-pink-100 text-pink-800 border border-pink-200">Scheduling</span>
                </div>
                <p class="text-xs text-slate-500 mt-1">Client consultation slots, calendar schedules, staff assignments, and deposit payments.</p>
            </div>

            <div class="flex items-center gap-2">
                <button @click="newBookingModal = true" class="px-3.5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold shadow-xs transition-colors flex items-center gap-1.5">
                    <span>+</span> Schedule Appointment
                </button>
            </div>
        </div>

        <!-- Metric Badges -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Bookings</div>
                <div class="text-xl font-bold font-heading text-slate-900">{{ number_format($totalBookings) }}</div>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Confirmed Sessions</div>
                <div class="text-xl font-bold font-heading text-emerald-600">{{ number_format($confirmedCount) }}</div>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Active Services Offered</div>
                <div class="text-xl font-bold font-heading text-slate-800">{{ $services->count() }} Consultations</div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="border-b border-slate-200 flex items-center gap-4 text-xs font-semibold">
            <a href="{{ route('apps.booking', ['tab' => 'appointments']) }}" class="pb-3 border-b-2 {{ $tab === 'appointments' ? 'border-red-600 text-red-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                Scheduled Appointments ({{ $bookings->total() }})
            </a>
            <a href="{{ route('apps.booking', ['tab' => 'services']) }}" class="pb-3 border-b-2 {{ $tab === 'services' ? 'border-red-600 text-red-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                Service Catalog & Pricing ({{ $services->count() }})
            </a>
        </div>

        @if($tab === 'appointments')
            <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] border-b border-slate-200">
                            <tr>
                                <th class="p-3">Booking Code</th>
                                <th class="p-3">Client Name</th>
                                <th class="p-3">Service</th>
                                <th class="p-3">Date</th>
                                <th class="p-3">Time Slot</th>
                                <th class="p-3">Assigned Staff</th>
                                <th class="p-3 text-right">Fee (KES)</th>
                                <th class="p-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($bookings as $b)
                                <tr class="hover:bg-slate-50">
                                    <td class="p-3 font-mono font-bold text-slate-900 whitespace-nowrap">{{ $b->booking_code }}</td>
                                    <td class="p-3">
                                        <div class="font-semibold text-slate-900">{{ $b->customer_name }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $b->customer_phone }}</div>
                                    </td>
                                    <td class="p-3 font-medium text-slate-900">{{ $b->service_name }}</td>
                                    <td class="p-3 whitespace-nowrap">{{ $b->booking_date->format('Y-m-d') }}</td>
                                    <td class="p-3 whitespace-nowrap font-mono text-[11px]">{{ $b->start_time }} - {{ $b->end_time }}</td>
                                    <td class="p-3">{{ $b->staff_name }}</td>
                                    <td class="p-3 text-right font-mono font-bold text-slate-900">{{ number_format($b->price_kes, 2) }}</td>
                                    <td class="p-3 text-center whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $b->status === 'confirmed' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                            {{ $b->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="p-8 text-center text-slate-400">No scheduled appointments yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div>
                {{ $bookings->links() }}
            </div>
        @endif

        @if($tab === 'services')
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse($services as $s)
                    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <h3 class="font-bold text-slate-900 text-sm font-heading">{{ $s->name }}</h3>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">{{ $s->duration_minutes }} Mins</span>
                            </div>
                            <p class="text-xs text-slate-500 mb-4">{{ $s->description ?? 'Professional consultation and advisory session.' }}</p>
                        </div>
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs text-slate-400">Session Fee</span>
                            <span class="text-sm font-bold text-slate-900 font-heading">KES {{ number_format($s->price_kes, 2) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full p-8 text-center bg-white border border-slate-200 rounded-xl text-slate-400 text-xs">
                        No services configured in catalog.
                    </div>
                @endforelse
            </div>
        @endif

        <!-- MODAL: Create Booking -->
        <div x-show="newBookingModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" style="display: none;">
            <div @click.away="newBookingModal = false" class="bg-white rounded-xl border border-slate-200 shadow-xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold font-heading text-slate-900">Book Client Appointment</h3>
                    <button @click="newBookingModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>
                <form action="{{ route('apps.booking.store') }}" method="POST" class="space-y-3.5">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Client Full Name</label>
                            <input type="text" name="customer_name" required placeholder="Brenda Chebet" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Phone Number</label>
                            <input type="text" name="customer_phone" required placeholder="+254 722 000 000" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Email Address (Optional)</label>
                        <input type="email" name="customer_email" placeholder="brenda@example.com" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Consultation / Service</label>
                        <select name="service_name" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">
                            @foreach($services as $s)
                                <option value="{{ $s->name }}">{{ $s->name }} (KES {{ number_format($s->price_kes, 0) }})</option>
                            @endforeach
                            <option value="General Strategy Consultation">General Strategy Consultation (KES 5,000)</option>
                            <option value="Brand Identity Review">Brand Identity Review (KES 7,500)</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Date</label>
                            <input type="date" name="booking_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Start Time</label>
                            <input type="text" name="start_time" value="10:00" required placeholder="10:00" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-mono">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">End Time</label>
                            <input type="text" name="end_time" value="11:30" required placeholder="11:30" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Total Fee (KES)</label>
                            <input type="number" step="0.01" name="price_kes" value="7500" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-bold">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Deposit Paid (KES)</label>
                            <input type="number" step="0.01" name="deposit_kes" value="2500" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="newBookingModal = false" class="px-4 py-2 border border-slate-300 rounded-lg text-xs text-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold">Confirm Booking</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
