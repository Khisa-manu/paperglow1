<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ClnConsultation;
use App\Models\ClnPatient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClinicManagerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $patientsQuery = ClnPatient::latest();
        $consultationsQuery = ClnConsultation::with('patient')->latest();

        if ($search) {
            $patientsQuery->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('patient_number', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $patients = $patientsQuery->paginate(15);
        $consultations = $consultationsQuery->paginate(15);
        $totalPatients = ClnPatient::count();
        $totalConsultations = ClnConsultation::count();

        return view('apps.clinic-manager', compact(
            'patients',
            'consultations',
            'totalPatients',
            'totalConsultations',
            'search'
        ));
    }

    public function storePatient(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'gender' => 'required|in:Male,Female,Other',
            'phone' => 'nullable|string|max:50',
            'national_id' => 'nullable|string|max:50',
            'dob' => 'nullable|date',
            'allergies' => 'nullable|string',
        ]);

        $patientNumber = 'PAT-' . date('Y') . '-' . rand(1000, 9999);

        $patient = ClnPatient::create(array_merge($validated, [
            'patient_number' => $patientNumber,
        ]));

        AuditLog::create([
            'organization_id' => session('active_organization_id'),
            'user_id' => Auth::id(),
            'action' => 'PATIENT_REGISTERED',
            'module' => 'Clinic Manager',
            'details' => "Registered patient {$patient->full_name} ({$patientNumber})",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('apps.clinic-manager')->with('success', "Patient {$patient->full_name} registered!");
    }

    public function storeConsultation(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:cln_patients,id',
            'doctor_name' => 'required|string|max:100',
            'symptoms' => 'nullable|string',
            'diagnosis' => 'required|string',
            'prescription' => 'nullable|string',
            'consultation_fee_kes' => 'required|numeric|min:0',
        ]);

        $consultation = ClnConsultation::create(array_merge($validated, [
            'payment_status' => 'paid',
        ]));

        $patient = ClnPatient::find($validated['patient_id']);

        AuditLog::create([
            'organization_id' => session('active_organization_id'),
            'user_id' => Auth::id(),
            'action' => 'CONSULTATION_RECORDED',
            'module' => 'Clinic Manager',
            'details' => "Consultation recorded for {$patient->full_name} by {$consultation->doctor_name}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('apps.clinic-manager')->with('success', "Clinical consultation recorded!");
    }
}
