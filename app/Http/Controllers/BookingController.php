<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BkBooking;
use App\Models\BkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'appointments');
        $search = $request->query('search');

        $bookingsQuery = BkBooking::latest();
        $services = BkService::where('is_active', true)->get();

        if ($search) {
            $bookingsQuery->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('booking_code', 'like', "%{$search}%")
                  ->orWhere('service_name', 'like', "%{$search}%");
            });
        }

        $bookings = $bookingsQuery->paginate(15);
        $totalBookings = BkBooking::count();
        $confirmedCount = BkBooking::where('status', 'confirmed')->count();

        return view('apps.booking', compact(
            'tab',
            'bookings',
            'services',
            'totalBookings',
            'confirmedCount',
            'search'
        ));
    }

    public function storeBooking(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'service_name' => 'required|string|max:255',
            'booking_date' => 'required|date',
            'start_time' => 'required|string|max:10',
            'end_time' => 'required|string|max:10',
            'price_kes' => 'required|numeric|min:0',
            'deposit_kes' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $bookingCode = 'BK-' . rand(1000, 9999);

        $booking = BkBooking::create(array_merge($validated, [
            'booking_code' => $bookingCode,
            'staff_name' => Auth::user()->name,
            'status' => 'confirmed',
            'payment_status' => (floatval($validated['deposit_kes'] ?? 0) >= floatval($validated['price_kes'])) ? 'paid' : 'deposit_paid',
        ]));

        AuditLog::create([
            'organization_id' => session('active_organization_id'),
            'user_id' => Auth::id(),
            'action' => 'BOOKING_CREATED',
            'module' => 'Booking',
            'details' => "Scheduled booking {$bookingCode} for {$booking->customer_name} ({$booking->service_name}) on {$booking->booking_date}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('apps.booking', ['tab' => 'appointments'])
            ->with('success', "Appointment {$bookingCode} confirmed!");
    }
}
