<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ====================================================================
        // BUSINESS MANAGER & INVOICE
        // ====================================================================
        Schema::create('bm_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->default('Nairobi');
            $table->string('kra_pin')->nullable();
            $table->decimal('outstanding_balance', 14, 2)->default(0.00);
            $table->decimal('total_spent', 14, 2)->default(0.00);
            $table->string('status', 30)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['organization_id', 'name']);
        });

        Schema::create('bm_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->enum('type', ['product', 'service'])->default('product');
            $table->string('category')->default('General');
            $table->decimal('stock_quantity', 12, 2)->default(0);
            $table->decimal('min_stock_threshold', 12, 2)->default(5);
            $table->decimal('buying_price', 14, 2)->default(0.00);
            $table->decimal('selling_price', 14, 2)->default(0.00);
            $table->string('unit')->default('pcs');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index(['organization_id', 'sku']);
        });

        Schema::create('bm_sales_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('bm_customers')->nullOnDelete();
            $table->string('document_number');
            $table->enum('document_type', ['invoice', 'quotation', 'receipt'])->default('invoice');
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_address')->nullable();
            $table->string('customer_kra_pin')->nullable();
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->json('items'); // JSON array of line items
            $table->decimal('subtotal', 14, 2)->default(0.00);
            $table->decimal('tax_rate', 5, 2)->default(16.00); // 16% VAT default in Kenya
            $table->decimal('tax_amount', 14, 2)->default(0.00);
            $table->decimal('discount_amount', 14, 2)->default(0.00);
            $table->decimal('grand_total', 14, 2)->default(0.00);
            $table->decimal('amount_paid', 14, 2)->default(0.00);
            $table->string('status', 30)->default('unpaid');
            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['organization_id', 'document_number']);
        });

        Schema::create('bm_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('voucher_number')->nullable();
            $table->string('category')->default('Office Supplies');
            $table->string('description');
            $table->decimal('amount', 14, 2)->default(0.00);
            $table->date('date');
            $table->string('payee')->nullable();
            $table->string('payment_method')->default('M-Pesa');
            $table->string('status', 30)->default('Paid');
            $table->timestamps();
            $table->index(['organization_id', 'date']);
        });

        Schema::create('bm_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('full_name');
            $table->string('role');
            $table->string('department')->default('Operations');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->decimal('base_salary', 14, 2)->default(0.00);
            $table->date('hire_date')->nullable();
            $table->string('status', 30)->default('Active');
            $table->timestamps();
        });

        // ====================================================================
        // PROPERTY MANAGER
        // ====================================================================
        Schema::create('prop_properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('property_type')->default('apartment_building');
            $table->string('location');
            $table->string('address')->nullable();
            $table->string('county')->default('Nairobi');
            $table->integer('total_units')->default(0);
            $table->string('caretaker_name')->nullable();
            $table->string('caretaker_phone')->nullable();
            $table->text('amenities')->nullable();
            $table->timestamps();
        });

        Schema::create('prop_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained('prop_properties')->cascadeOnDelete();
            $table->string('unit_number');
            $table->integer('floor')->default(1);
            $table->string('unit_type')->default('2 Bedroom');
            $table->decimal('monthly_rent_kes', 12, 2)->default(0.00);
            $table->decimal('deposit_kes', 12, 2)->default(0.00);
            $table->enum('status', ['occupied', 'vacant', 'maintenance'])->default('vacant');
            $table->timestamps();
        });

        Schema::create('prop_tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->nullable()->constrained('prop_properties')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('prop_units')->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('national_id')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->date('move_in_date')->nullable();
            $table->decimal('monthly_rent_kes', 12, 2)->default(0.00);
            $table->decimal('balance_kes', 12, 2)->default(0.00);
            $table->string('status', 30)->default('active');
            $table->timestamps();
        });

        Schema::create('prop_rent_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->nullable()->constrained('prop_properties')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('prop_units')->nullOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained('prop_tenants')->nullOnDelete();
            $table->string('receipt_number');
            $table->decimal('amount_kes', 12, 2)->default(0.00);
            $table->string('month_for');
            $table->date('payment_date');
            $table->string('payment_method')->default('M-Pesa');
            $table->string('transaction_reference')->nullable();
            $table->string('status', 30)->default('confirmed');
            $table->timestamps();
        });

        Schema::create('prop_maintenance_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained('prop_properties')->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('prop_units')->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('category')->default('plumbing');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['reported', 'in_progress', 'resolved'])->default('reported');
            $table->decimal('cost_kes', 12, 2)->default(0.00);
            $table->timestamps();
        });

        // ====================================================================
        // PHARMACY MANAGER
        // ====================================================================
        Schema::create('pharm_medicines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('generic_name')->nullable();
            $table->string('category')->default('Antibiotics');
            $table->string('manufacturer')->nullable();
            $table->string('sku_barcode')->nullable();
            $table->string('batch_number')->nullable();
            $table->string('unit_of_measure')->default('Pack');
            $table->integer('quantity_in_stock')->default(0);
            $table->integer('min_stock_level')->default(10);
            $table->decimal('buying_price_kes', 12, 2)->default(0.00);
            $table->decimal('selling_price_kes', 12, 2)->default(0.00);
            $table->date('expiry_date')->nullable();
            $table->boolean('requires_prescription')->default(false);
            $table->string('shelf_location')->nullable();
            $table->timestamps();
        });

        Schema::create('pharm_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('receipt_number');
            $table->string('customer_name')->default('Walk-in Patient');
            $table->string('customer_phone')->nullable();
            $table->json('items');
            $table->decimal('subtotal_kes', 12, 2)->default(0.00);
            $table->decimal('discount_kes', 12, 2)->default(0.00);
            $table->decimal('total_amount_kes', 12, 2)->default(0.00);
            $table->string('payment_method')->default('mpesa');
            $table->string('mpesa_ref')->nullable();
            $table->string('dispensed_by')->default('Pharmacist');
            $table->timestamps();
        });

        Schema::create('pharm_suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('company_name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->decimal('outstanding_balance_kes', 12, 2)->default(0.00);
            $table->timestamps();
        });

        // ====================================================================
        // CHAMA / SACCO MANAGER
        // ====================================================================
        Schema::create('chama_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('registration_number')->nullable();
            $table->enum('cycle_frequency', ['weekly', 'monthly'])->default('monthly');
            $table->decimal('monthly_contribution_kes', 12, 2)->default(2000.00);
            $table->decimal('welfare_monthly_kes', 12, 2)->default(200.00);
            $table->decimal('loan_interest_rate_percent', 5, 2)->default(10.00);
            $table->decimal('total_group_savings_kes', 14, 2)->default(0.00);
            $table->timestamps();
        });

        Schema::create('chama_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('group_id')->nullable()->constrained('chama_groups')->nullOnDelete();
            $table->string('membership_number')->nullable();
            $table->string('name');
            $table->string('national_id')->nullable();
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('role_in_chama')->default('Member'); // Chairperson, Treasurer, Secretary, Member
            $table->decimal('total_contributions_kes', 14, 2)->default(0.00);
            $table->decimal('total_loans_taken_kes', 14, 2)->default(0.00);
            $table->decimal('outstanding_loan_balance_kes', 14, 2)->default(0.00);
            $table->string('status', 30)->default('Active');
            $table->timestamps();
        });

        Schema::create('chama_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('chama_members')->cascadeOnDelete();
            $table->string('receipt_number');
            $table->decimal('savings_amount_kes', 12, 2)->default(0.00);
            $table->decimal('welfare_amount_kes', 12, 2)->default(0.00);
            $table->decimal('total_amount_kes', 12, 2)->default(0.00);
            $table->string('month_period');
            $table->date('payment_date');
            $table->string('payment_method')->default('M-Pesa');
            $table->string('reference_code')->nullable();
            $table->timestamps();
        });

        Schema::create('chama_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('chama_members')->cascadeOnDelete();
            $table->decimal('principal_kes', 12, 2);
            $table->decimal('interest_rate_percent', 5, 2)->default(10.00);
            $table->decimal('total_payable_kes', 12, 2);
            $table->decimal('amount_repaid_kes', 12, 2)->default(0.00);
            $table->integer('duration_months')->default(3);
            $table->date('disbursed_date');
            $table->date('due_date');
            $table->enum('status', ['active', 'cleared', 'defaulted'])->default('active');
            $table->timestamps();
        });

        // ====================================================================
        // TICKETING & HELPDESK
        // ====================================================================
        Schema::create('tck_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('ticket_code');
            $table->string('subject');
            $table->text('description');
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('category')->default('Technical Support');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'pending', 'resolved', 'closed'])->default('open');
            $table->string('assigned_staff_name')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->boolean('is_overdue')->default(false);
            $table->timestamps();
        });

        // ====================================================================
        // BOOKING & APPOINTMENTS
        // ====================================================================
        Schema::create('bk_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->integer('duration_minutes')->default(60);
            $table->decimal('price_kes', 12, 2)->default(0.00);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('bk_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('booking_code');
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->string('service_name');
            $table->string('staff_name')->default('Specialist');
            $table->date('booking_date');
            $table->string('start_time', 10);
            $table->string('end_time', 10);
            $table->decimal('price_kes', 12, 2)->default(0.00);
            $table->decimal('deposit_kes', 12, 2)->default(0.00);
            $table->string('status', 30)->default('confirmed');
            $table->string('payment_status', 30)->default('paid');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ====================================================================
        // STOCK & INVENTORY
        // ====================================================================
        Schema::create('inv_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            $table->string('category')->default('General');
            $table->string('supplier_name')->nullable();
            $table->integer('current_quantity')->default(0);
            $table->integer('min_stock_level')->default(5);
            $table->integer('max_stock_level')->default(100);
            $table->decimal('buying_price_kes', 12, 2)->default(0.00);
            $table->decimal('selling_price_kes', 12, 2)->default(0.00);
            $table->string('unit')->default('Pcs');
            $table->string('location')->nullable();
            $table->string('status', 30)->default('in_stock');
            $table->timestamps();
        });

        // ====================================================================
        // LEGAL PRACTICE
        // ====================================================================
        Schema::create('leg_matters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('matter_number');
            $table->string('title');
            $table->string('client_name');
            $table->string('practice_area')->default('Commercial Litigation');
            $table->string('court_forum')->nullable();
            $table->string('assigned_advocate')->default('Lead Advocate');
            $table->date('filing_date')->nullable();
            $table->date('next_court_date')->nullable();
            $table->string('opposing_party')->nullable();
            $table->decimal('total_billed_kes', 14, 2)->default(0.00);
            $table->decimal('total_paid_kes', 14, 2)->default(0.00);
            $table->string('status', 30)->default('open');
            $table->timestamps();
        });

        // ====================================================================
        // SCHOOL MANAGER
        // ====================================================================
        Schema::create('sch_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('admission_number');
            $table->string('full_name');
            $table->string('grade_class');
            $table->string('guardian_name');
            $table->string('guardian_phone');
            $table->decimal('fee_balance_kes', 12, 2)->default(0.00);
            $table->string('status', 30)->default('active');
            $table->timestamps();
        });

        // ====================================================================
        // CLINIC MANAGER
        // ====================================================================
        Schema::create('cln_patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('patient_number');
            $table->string('full_name');
            $table->string('gender')->default('Other');
            $table->string('phone')->nullable();
            $table->string('national_id')->nullable();
            $table->date('dob')->nullable();
            $table->text('allergies')->nullable();
            $table->timestamps();
        });

        Schema::create('cln_consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('cln_patients')->cascadeOnDelete();
            $table->string('doctor_name')->default('Dr. Ochieng');
            $table->text('symptoms')->nullable();
            $table->text('diagnosis');
            $table->text('prescription')->nullable();
            $table->decimal('consultation_fee_kes', 10, 2)->default(1500.00);
            $table->string('payment_status', 30)->default('paid');
            $table->timestamps();
        });

        // ====================================================================
        // CRM & CONTRACTS
        // ====================================================================
        Schema::create('crm_deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('company');
            $table->string('contact_person');
            $table->string('contact_email')->nullable();
            $table->decimal('deal_value_kes', 14, 2)->default(0.00);
            $table->enum('stage', ['lead', 'contacted', 'proposal', 'negotiation', 'won', 'lost'])->default('lead');
            $table->date('expected_close_date')->nullable();
            $table->timestamps();
        });

        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('contract_number');
            $table->string('title');
            $table->string('party_name');
            $table->decimal('contract_value_kes', 14, 2)->default(0.00);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 30)->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('crm_deals');
        Schema::dropIfExists('cln_consultations');
        Schema::dropIfExists('cln_patients');
        Schema::dropIfExists('sch_students');
        Schema::dropIfExists('leg_matters');
        Schema::dropIfExists('inv_products');
        Schema::dropIfExists('bk_bookings');
        Schema::dropIfExists('bk_services');
        Schema::dropIfExists('tck_tickets');
        Schema::dropIfExists('chama_loans');
        Schema::dropIfExists('chama_contributions');
        Schema::dropIfExists('chama_members');
        Schema::dropIfExists('chama_groups');
        Schema::dropIfExists('pharm_suppliers');
        Schema::dropIfExists('pharm_sales');
        Schema::dropIfExists('pharm_medicines');
        Schema::dropIfExists('prop_maintenance_tickets');
        Schema::dropIfExists('prop_rent_payments');
        Schema::dropIfExists('prop_tenants');
        Schema::dropIfExists('prop_units');
        Schema::dropIfExists('prop_properties');
        Schema::dropIfExists('bm_employees');
        Schema::dropIfExists('bm_expenses');
        Schema::dropIfExists('bm_sales_documents');
        Schema::dropIfExists('bm_products');
        Schema::dropIfExists('bm_customers');
    }
};
