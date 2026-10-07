<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ChamaContribution;
use App\Models\ChamaGroup;
use App\Models\ChamaLoan;
use App\Models\ChamaMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChamaManagerController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'members');
        $search = $request->query('search');

        $groups = ChamaGroup::with(['members'])->get();
        $membersQuery = ChamaMember::with('group')->latest();
        $contributionsQuery = ChamaContribution::with('member')->latest();
        $loansQuery = ChamaLoan::with('member')->latest();

        if ($search) {
            $membersQuery->where('name', 'like', "%{$search}%");
            $contributionsQuery->where('receipt_number', 'like', "%{$search}%");
        }

        $members = $membersQuery->paginate(20);
        $contributions = $contributionsQuery->paginate(15);
        $loans = $loansQuery->paginate(15);

        $totalMembers = ChamaMember::count();
        $totalContributionsKes = ChamaContribution::sum('total_amount_kes');
        $totalActiveLoansKes = ChamaLoan::where('status', 'active')->sum('total_payable_kes');

        return view('apps.chama-manager', compact(
            'tab',
            'groups',
            'members',
            'contributions',
            'loans',
            'totalMembers',
            'totalContributionsKes',
            'totalActiveLoansKes',
            'search'
        ));
    }

    public function storeMember(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'national_id' => 'nullable|string|max:50',
            'role_in_chama' => 'required|string|max:50',
            'membership_number' => 'nullable|string|max:50',
        ]);

        $group = ChamaGroup::first();

        $member = ChamaMember::create(array_merge($validated, [
            'group_id' => $group ? $group->id : null,
            'membership_number' => $validated['membership_number'] ?? ('MEM-' . rand(100, 999)),
            'status' => 'Active',
        ]));

        AuditLog::create([
            'organization_id' => session('active_organization_id'),
            'user_id' => Auth::id(),
            'action' => 'CHAMA_MEMBER_ENROLLED',
            'module' => 'Chama Manager',
            'details' => "Enrolled {$member->name} as {$member->role_in_chama}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('apps.chama-manager', ['tab' => 'members'])
            ->with('success', "Member {$member->name} added to Chama register!");
    }

    public function recordContribution(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:chama_members,id',
            'savings_amount_kes' => 'required|numeric|min:0',
            'welfare_amount_kes' => 'nullable|numeric|min:0',
            'month_period' => 'required|string|max:100',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|max:50',
            'reference_code' => 'nullable|string|max:50',
        ]);

        $savings = floatval($validated['savings_amount_kes']);
        $welfare = floatval($validated['welfare_amount_kes'] ?? 0);
        $total = $savings + $welfare;

        $receiptNumber = 'CHM-' . date('Y') . '-' . rand(1000, 9999);

        $contrib = ChamaContribution::create(array_merge($validated, [
            'total_amount_kes' => $total,
            'receipt_number' => $receiptNumber,
        ]));

        $member = ChamaMember::findOrFail($validated['member_id']);
        $member->increment('total_contributions_kes', $total);

        $group = ChamaGroup::first();
        if ($group) {
            $group->increment('total_group_savings_kes', $savings);
        }

        AuditLog::create([
            'organization_id' => session('active_organization_id'),
            'user_id' => Auth::id(),
            'action' => 'CHAMA_CONTRIBUTION_RECORDED',
            'module' => 'Chama Manager',
            'details' => "Receipt {$receiptNumber}: KES {$total} from {$member->name} for {$validated['month_period']}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('apps.chama-manager', ['tab' => 'contributions'])
            ->with('success', "Contribution receipt {$receiptNumber} recorded!");
    }
}
