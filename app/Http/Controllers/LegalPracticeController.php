<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\LegMatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LegalPracticeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $query = LegMatter::latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('matter_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('client_name', 'like', "%{$search}%");
            });
        }

        $matters = $query->paginate(15);
        $totalMatters = LegMatter::count();
        $totalBilledKes = LegMatter::sum('total_billed_kes');
        $activeMattersCount = LegMatter::where('status', 'open')->count();

        return view('apps.legal-practice', compact(
            'matters',
            'totalMatters',
            'totalBilledKes',
            'activeMattersCount',
            'search'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'matter_number' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
            'practice_area' => 'required|string|max:100',
            'court_forum' => 'nullable|string|max:150',
            'assigned_advocate' => 'required|string|max:100',
            'filing_date' => 'nullable|date',
            'next_court_date' => 'nullable|date',
            'total_billed_kes' => 'nullable|numeric|min:0',
        ]);

        $matter = LegMatter::create(array_merge($validated, [
            'total_billed_kes' => $validated['total_billed_kes'] ?? 0.00,
            'total_paid_kes' => 0.00,
            'status' => 'open',
        ]));

        AuditLog::create([
            'organization_id' => session('active_organization_id'),
            'user_id' => Auth::id(),
            'action' => 'LEGAL_MATTER_FILED',
            'module' => 'Legal Practice',
            'details' => "Filed matter {$matter->matter_number}: {$matter->title} for {$matter->client_name}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('apps.legal-practice')->with('success', "Matter {$matter->matter_number} opened successfully!");
    }
}
