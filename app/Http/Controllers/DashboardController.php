<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\AuditLog;
use App\Models\BkBooking;
use App\Models\BmCustomer;
use App\Models\BmSalesDocument;
use App\Models\ChamaGroup;
use App\Models\ChamaMember;
use App\Models\Notification;
use App\Models\PharmMedicine;
use App\Models\PropProperty;
use App\Models\PropTenant;
use App\Models\TckTicket;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $org = $user->currentOrganization();
        if (!$org) {
            return redirect()->route('login');
        }

        // Live MariaDB Metrics isolated by Organization
        $totalCustomers = BmCustomer::count();
        $totalRevenueKes = BmSalesDocument::where('status', 'paid')->sum('grand_total');
        $totalInvoicesCount = BmSalesDocument::count();
        $totalProperties = PropProperty::count();
        $totalTenants = PropTenant::where('status', 'active')->count();
        $totalMedicines = PharmMedicine::count();
        $totalChamaMembers = ChamaMember::count();
        $totalChamaSavings = ChamaGroup::sum('total_group_savings_kes');
        $openTicketsCount = TckTicket::whereIn('status', ['open', 'in_progress'])->count();
        $upcomingBookingsCount = BkBooking::where('booking_date', '>=', now()->toDateString())->count();

        // Subscribed applications
        $subscribedSlugs = $org->subscriptions()->whereIn('status', ['active', 'trial'])->pluck('app_slug')->toArray();
        $availableApps = Application::whereIn('slug', $subscribedSlugs)->get();

        // Recent Audit Logs
        $recentAudits = AuditLog::latest()->take(6)->get();

        // Recent Invoices
        $recentInvoices = BmSalesDocument::latest()->take(5)->get();

        return view('dashboard', compact(
            'org',
            'totalCustomers',
            'totalRevenueKes',
            'totalInvoicesCount',
            'totalProperties',
            'totalTenants',
            'totalMedicines',
            'totalChamaMembers',
            'totalChamaSavings',
            'openTicketsCount',
            'upcomingBookingsCount',
            'availableApps',
            'recentAudits',
            'recentInvoices'
        ));
    }
}
