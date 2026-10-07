<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\SchStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolManagerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $query = SchStudent::latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('admission_number', 'like', "%{$search}%")
                  ->orWhere('guardian_name', 'like', "%{$search}%");
            });
        }

        $students = $query->paginate(20);
        $totalStudents = SchStudent::count();
        $totalFeeBalanceKes = SchStudent::sum('fee_balance_kes');

        return view('apps.school-manager', compact(
            'students',
            'totalStudents',
            'totalFeeBalanceKes',
            'search'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'admission_number' => 'required|string|max:50',
            'full_name' => 'required|string|max:255',
            'grade_class' => 'required|string|max:100',
            'guardian_name' => 'required|string|max:255',
            'guardian_phone' => 'required|string|max:50',
            'fee_balance_kes' => 'nullable|numeric|min:0',
        ]);

        $student = SchStudent::create(array_merge($validated, [
            'status' => 'active',
            'fee_balance_kes' => $validated['fee_balance_kes'] ?? 0.00,
        ]));

        AuditLog::create([
            'organization_id' => session('active_organization_id'),
            'user_id' => Auth::id(),
            'action' => 'STUDENT_ENROLLED',
            'module' => 'School Manager',
            'details' => "Enrolled student {$student->full_name} (Adm: {$student->admission_number}, Grade: {$student->grade_class})",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('apps.school-manager')->with('success', "Student {$student->full_name} registered!");
    }
}
