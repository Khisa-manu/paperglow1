<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\TckTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketingController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $priority = $request->query('priority');
        $search = $request->query('search');

        $query = TckTicket::latest();

        if ($status) {
            $query->where('status', $status);
        }
        if ($priority) {
            $query->where('priority', $priority);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('ticket_code', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        $tickets = $query->paginate(15);
        $totalTickets = TckTicket::count();
        $openCount = TckTicket::where('status', 'open')->count();
        $inProgressCount = TckTicket::where('status', 'in_progress')->count();
        $resolvedCount = TckTicket::whereIn('status', ['resolved', 'closed'])->count();

        return view('apps.ticketing', compact(
            'tickets',
            'totalTickets',
            'openCount',
            'inProgressCount',
            'resolvedCount',
            'status',
            'priority',
            'search'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'category' => 'required|string|max:100',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $ticketCode = 'TCK-' . date('Y') . '-' . rand(1000, 9999);

        $ticket = TckTicket::create(array_merge($validated, [
            'ticket_code' => $ticketCode,
            'status' => 'open',
            'assigned_staff_name' => Auth::user()->name,
            'due_date' => now()->addHours(24),
        ]));

        AuditLog::create([
            'organization_id' => session('active_organization_id'),
            'user_id' => Auth::id(),
            'action' => 'TICKET_CREATED',
            'module' => 'Ticketing',
            'details' => "Opened ticket {$ticketCode}: {$ticket->subject}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('apps.ticketing')->with('success', "Ticket {$ticketCode} opened successfully!");
    }

    public function updateStatus(Request $request, TckTicket $ticket)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,pending,resolved,closed',
        ]);

        $oldStatus = $ticket->status;
        $ticket->update(['status' => $validated['status']]);

        AuditLog::create([
            'organization_id' => session('active_organization_id'),
            'user_id' => Auth::id(),
            'action' => 'TICKET_STATUS_UPDATED',
            'module' => 'Ticketing',
            'details' => "Ticket {$ticket->ticket_code} status changed from {$oldStatus} to {$validated['status']}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->back()->with('success', "Ticket {$ticket->ticket_code} status updated to {$validated['status']}!");
    }
}
