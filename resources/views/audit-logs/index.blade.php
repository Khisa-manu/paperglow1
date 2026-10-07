<x-layouts.app title="Audit Logs">
    <div class="space-y-6 max-w-5xl">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold font-heading text-slate-900 tracking-tight">Organization Audit Trails</h1>
                <p class="text-xs text-slate-500 mt-1">Immutable security and operations log for <strong class="text-slate-800">{{ $currentOrg->name }}</strong>.</p>
            </div>

            <!-- Filter by Module -->
            <form action="{{ route('audit-logs.index') }}" method="GET" class="flex items-center gap-2">
                <select name="module" onchange="this.form.submit()" class="px-3 py-1.5 border border-slate-300 rounded-lg text-xs bg-white text-slate-700">
                    <option value="">All Modules</option>
                    @foreach($modules as $m)
                        <option value="{{ $m }}" {{ $module === $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] border-b border-slate-200">
                        <tr>
                            <th class="p-3">Timestamp</th>
                            <th class="p-3">Action</th>
                            <th class="p-3">Module</th>
                            <th class="p-3">Details</th>
                            <th class="p-3">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-50">
                                <td class="p-3 whitespace-nowrap text-slate-400 font-mono">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                                <td class="p-3 font-semibold text-slate-900">{{ str_replace('_', ' ', $log->action) }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                        {{ $log->module }}
                                    </span>
                                </td>
                                <td class="p-3 max-w-xs truncate">{{ $log->details }}</td>
                                <td class="p-3 font-mono text-slate-400">{{ $log->ip_address ?? '127.0.0.1' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-6 text-center text-slate-400">No logs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            {{ $logs->links() }}
        </div>
    </div>
</x-layouts.app>
