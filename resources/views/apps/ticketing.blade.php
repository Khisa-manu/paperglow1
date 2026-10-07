<x-layouts.app title="Ticketing & Helpdesk">
    <div class="space-y-6" x-data="{ newTicketModal: false }">
        <!-- App Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold font-heading text-slate-900 tracking-tight">Ticketing & Helpdesk</h1>
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-cyan-100 text-cyan-800 border border-cyan-200">Customer Support</span>
                </div>
                <p class="text-xs text-slate-500 mt-1">Multi-tier support queue, incident escalation, client communication and resolution tracking.</p>
            </div>

            <div class="flex items-center gap-2">
                <button @click="newTicketModal = true" class="px-3.5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold shadow-xs transition-colors flex items-center gap-1.5">
                    <span>+</span> Open New Ticket
                </button>
            </div>
        </div>

        <!-- Metric Badges -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Incidents</div>
                <div class="text-xl font-bold font-heading text-slate-900">{{ number_format($totalTickets) }}</div>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Open Tickets</div>
                <div class="text-xl font-bold font-heading text-amber-600">{{ number_format($openCount) }}</div>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">In Progress</div>
                <div class="text-xl font-bold font-heading text-blue-600">{{ number_format($inProgressCount) }}</div>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Resolved / Closed</div>
                <div class="text-xl font-bold font-heading text-emerald-600">{{ number_format($resolvedCount) }}</div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white border border-slate-200 rounded-xl p-3 shadow-xs flex flex-wrap items-center justify-between gap-3 text-xs">
            <form action="{{ route('apps.ticketing') }}" method="GET" class="flex flex-wrap items-center gap-2 flex-1">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search code, client or subject..." class="px-3 py-1.5 border border-slate-300 rounded-lg text-xs w-64 focus:ring-2 focus:ring-red-500 focus:outline-none">
                
                <select name="status" onchange="this.form.submit()" class="px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs bg-white text-slate-700">
                    <option value="">All Statuses</option>
                    <option value="open" {{ $status === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="in_progress" {{ $status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="resolved" {{ $status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="closed" {{ $status === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>

                <select name="priority" onchange="this.form.submit()" class="px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs bg-white text-slate-700">
                    <option value="">All Priorities</option>
                    <option value="low" {{ $priority === 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ $priority === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ $priority === 'high' ? 'selected' : '' }}>High</option>
                    <option value="urgent" {{ $priority === 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>

                <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg">Filter</button>
            </form>
        </div>

        <!-- Tickets Table -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] border-b border-slate-200">
                        <tr>
                            <th class="p-3">Ticket Code</th>
                            <th class="p-3">Client</th>
                            <th class="p-3">Subject & Category</th>
                            <th class="p-3 text-center">Priority</th>
                            <th class="p-3 text-center">Status</th>
                            <th class="p-3">Assigned Staff</th>
                            <th class="p-3">Created</th>
                            <th class="p-3 text-right">Update Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($tickets as $t)
                            <tr class="hover:bg-slate-50">
                                <td class="p-3 font-mono font-bold text-slate-900 whitespace-nowrap">{{ $t->ticket_code }}</td>
                                <td class="p-3">
                                    <div class="font-semibold text-slate-900">{{ $t->customer_name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $t->customer_email }}</div>
                                </td>
                                <td class="p-3 max-w-sm">
                                    <div class="font-medium text-slate-900 truncate">{{ $t->subject }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $t->category }}</div>
                                </td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $t->priority === 'urgent' ? 'bg-red-100 text-red-800' : ($t->priority === 'high' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">
                                        {{ $t->priority }}
                                    </span>
                                </td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $t->status === 'resolved' || $t->status === 'closed' ? 'bg-emerald-50 text-emerald-700' : ($t->status === 'in_progress' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700') }}">
                                        {{ str_replace('_', ' ', $t->status) }}
                                    </span>
                                </td>
                                <td class="p-3 whitespace-nowrap">{{ $t->assigned_staff_name ?? 'Unassigned' }}</td>
                                <td class="p-3 whitespace-nowrap text-slate-400 text-[10px]">{{ $t->created_at->diffForHumans() }}</td>
                                <td class="p-3 text-right">
                                    <form action="{{ route('apps.ticketing.status', $t) }}" method="POST" class="inline-flex items-center">
                                        @csrf
                                        <select name="status" onchange="this.form.submit()" class="text-[11px] px-2 py-1 border border-slate-200 rounded-md bg-white text-slate-700 font-medium">
                                            <option value="open" {{ $t->status === 'open' ? 'selected' : '' }}>Open</option>
                                            <option value="in_progress" {{ $t->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                            <option value="pending" {{ $t->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="resolved" {{ $t->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                            <option value="closed" {{ $t->status === 'closed' ? 'selected' : '' }}>Closed</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center text-slate-400">No support tickets found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            {{ $tickets->links() }}
        </div>

        <!-- MODAL: Create Ticket -->
        <div x-show="newTicketModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" style="display: none;">
            <div @click.away="newTicketModal = false" class="bg-white rounded-xl border border-slate-200 shadow-xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold font-heading text-slate-900">Open Customer Support Ticket</h3>
                    <button @click="newTicketModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>
                <form action="{{ route('apps.ticketing.store') }}" method="POST" class="space-y-3.5">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Customer / Contact Name</label>
                            <input type="text" name="customer_name" required placeholder="Alice Wanjiru" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Email Address</label>
                            <input type="email" name="customer_email" required placeholder="alice@client.co.ke" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Ticket Subject</label>
                        <input type="text" name="subject" required placeholder="e.g. Production Delay on Promotional Hoodies" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Category</label>
                            <select name="category" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">
                                <option value="Production & Orders">Production & Orders</option>
                                <option value="Design Proofs">Design Proofs</option>
                                <option value="Billing & Invoices">Billing & Invoices</option>
                                <option value="Technical Support">Technical Support</option>
                                <option value="DirectAdmin Hosting">DirectAdmin Hosting</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Priority Level</label>
                            <select name="priority" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase mb-1">Description / Issue Summary</label>
                        <textarea name="description" rows="3" required placeholder="Detailed notes regarding customer inquiry or reported defect..." class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="newTicketModal = false" class="px-4 py-2 border border-slate-300 rounded-lg text-xs text-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold">Open Ticket</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
