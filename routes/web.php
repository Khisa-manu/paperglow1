<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BusinessManagerController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ChamaManagerController;
use App\Http\Controllers\ClinicManagerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LegalPracticeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PharmacyManagerController;
use App\Http\Controllers\PropertyManagerController;
use App\Http\Controllers\SchoolManagerController;
use App\Http\Controllers\StockInventoryController;
use App\Http\Controllers\TicketingController;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated SaaS Platform Routes (Guard ordered: auth -> organization)
Route::middleware(['auth', 'organization'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Multi-tenant Workspace & Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/organizations/switch/{organization}', [OrganizationController::class, 'switch'])->name('organizations.switch');
    Route::post('/organizations', [OrganizationController::class, 'store'])->name('organizations.store');
    Route::get('/organizations/members', [OrganizationController::class, 'members'])->name('organizations.members');
    Route::post('/organizations/members', [OrganizationController::class, 'addMember'])->name('organizations.members.add');

    // SaaS Catalog & Subscriptions
    Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
    Route::post('/catalog/toggle/{appSlug}', [CatalogController::class, 'toggle'])->name('catalog.toggle');

    // Notifications & Audit Logs
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    // Applications Modules
    Route::prefix('apps')->name('apps.')->group(function () {
        // Business Manager
        Route::get('/business-manager', [BusinessManagerController::class, 'index'])->name('business-manager');
        Route::post('/business-manager/customer', [BusinessManagerController::class, 'storeCustomer'])->name('business-manager.customer');
        Route::post('/business-manager/product', [BusinessManagerController::class, 'storeProduct'])->name('business-manager.product');
        Route::post('/business-manager/invoice', [BusinessManagerController::class, 'storeInvoice'])->name('business-manager.invoice');
        Route::post('/business-manager/expense', [BusinessManagerController::class, 'storeExpense'])->name('business-manager.expense');
        Route::delete('/business-manager/document/{doc}', [BusinessManagerController::class, 'deleteDocument'])->name('business-manager.delete-doc');

        // Aliases for Invoice & CRM
        Route::get('/invoices', [BusinessManagerController::class, 'index'])->name('invoices');
        Route::get('/crm', [BusinessManagerController::class, 'index'])->name('crm');

        // Property Manager
        Route::get('/property-manager', [PropertyManagerController::class, 'index'])->name('property-manager');
        Route::post('/property-manager/property', [PropertyManagerController::class, 'storeProperty'])->name('property-manager.property');
        Route::post('/property-manager/tenant', [PropertyManagerController::class, 'storeTenant'])->name('property-manager.tenant');
        Route::post('/property-manager/payment', [PropertyManagerController::class, 'storePayment'])->name('property-manager.payment');

        // Pharmacy Manager
        Route::get('/pharmacy-manager', [PharmacyManagerController::class, 'index'])->name('pharmacy-manager');
        Route::post('/pharmacy-manager/medicine', [PharmacyManagerController::class, 'storeMedicine'])->name('pharmacy-manager.medicine');
        Route::post('/pharmacy-manager/sale', [PharmacyManagerController::class, 'recordSale'])->name('pharmacy-manager.sale');

        // Chama Manager
        Route::get('/chama-manager', [ChamaManagerController::class, 'index'])->name('chama-manager');
        Route::post('/chama-manager/member', [ChamaManagerController::class, 'storeMember'])->name('chama-manager.member');
        Route::post('/chama-manager/contribution', [ChamaManagerController::class, 'recordContribution'])->name('chama-manager.contribution');

        // Ticketing / Helpdesk
        Route::get('/ticketing', [TicketingController::class, 'index'])->name('ticketing');
        Route::post('/ticketing/ticket', [TicketingController::class, 'store'])->name('ticketing.store');
        Route::post('/ticketing/{ticket}/status', [TicketingController::class, 'updateStatus'])->name('ticketing.status');

        // Booking & Appointments
        Route::get('/booking', [BookingController::class, 'index'])->name('booking');
        Route::post('/booking/booking', [BookingController::class, 'storeBooking'])->name('booking.store');

        // Stock Inventory
        Route::get('/stock-inventory', [StockInventoryController::class, 'index'])->name('stock-inventory');
        Route::post('/stock-inventory/product', [StockInventoryController::class, 'store'])->name('stock-inventory.product');
        Route::post('/stock-inventory/{product}/adjust', [StockInventoryController::class, 'adjustStock'])->name('stock-inventory.adjust');

        // Legal Practice
        Route::get('/legal-practice', [LegalPracticeController::class, 'index'])->name('legal-practice');
        Route::post('/legal-practice/matter', [LegalPracticeController::class, 'store'])->name('legal-practice.store');

        // School Manager
        Route::get('/school-manager', [SchoolManagerController::class, 'index'])->name('school-manager');
        Route::post('/school-manager/student', [SchoolManagerController::class, 'storeStudent'])->name('school-manager.student');

        // Clinic Manager
        Route::get('/clinic-manager', [ClinicManagerController::class, 'index'])->name('clinic-manager');
        Route::post('/clinic-manager/patient', [ClinicManagerController::class, 'storePatient'])->name('clinic-manager.patient');
        Route::post('/clinic-manager/consultation', [ClinicManagerController::class, 'storeConsultation'])->name('clinic-manager.consultation');

        // Team / Workforce alias
        Route::get('/team', [OrganizationController::class, 'members'])->name('team');

        // Contracts & Agreements alias
        Route::get('/contracts', [LegalPracticeController::class, 'index'])->name('contracts');
    });
});
