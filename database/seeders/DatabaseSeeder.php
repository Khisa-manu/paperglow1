<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Applications Catalog
        $apps = [
            [
                'slug' => 'business-manager',
                'name' => 'Business Manager',
                'category' => 'Operations & ERP',
                'description' => 'Complete operations management with sales documents, invoices, receipts, expenses, customers, and staff tracking.',
                'icon' => 'briefcase',
                'monthly_price_kes' => 4500.00,
            ],
            [
                'slug' => 'invoices',
                'name' => 'Invoice & Quotations',
                'category' => 'Finance & Accounting',
                'description' => 'Professional electronic invoices, quotations, VAT/KRA compliance, M-Pesa receipts and automated billing.',
                'icon' => 'file-text',
                'monthly_price_kes' => 2500.00,
            ],
            [
                'slug' => 'crm',
                'name' => 'CRM & Pipeline',
                'category' => 'Sales & Growth',
                'description' => 'Client lead management, deal pipelines, customer interactions and conversion performance.',
                'icon' => 'users',
                'monthly_price_kes' => 3000.00,
            ],
            [
                'slug' => 'property-manager',
                'name' => 'Property Manager',
                'category' => 'Real Estate',
                'description' => 'Multi-property estate management, unit occupancy, tenant leases, automated rent schedules and maintenance tickets.',
                'icon' => 'building',
                'monthly_price_kes' => 6000.00,
            ],
            [
                'slug' => 'pharmacy-manager',
                'name' => 'Pharmacy Manager',
                'category' => 'Healthcare & Retail',
                'description' => 'Dispensary POS, batch expiry tracking, PPB compliance, cold chain stock, and prescription recording.',
                'icon' => 'pill',
                'monthly_price_kes' => 5500.00,
            ],
            [
                'slug' => 'chama-manager',
                'name' => 'Chama & Sacco Manager',
                'category' => 'Community Finance',
                'description' => 'Merry-go-round and investment group management, contributions, micro-loans, welfare claims, and meeting minutes.',
                'icon' => 'piggy-bank',
                'monthly_price_kes' => 3500.00,
            ],
            [
                'slug' => 'ticketing',
                'name' => 'Ticketing & Helpdesk',
                'category' => 'Customer Care',
                'description' => 'Customer support ticketing, SLA tracking, multi-tier agent assignment, satisfaction scoring and issue resolution.',
                'icon' => 'life-buoy',
                'monthly_price_kes' => 3000.00,
            ],
            [
                'slug' => 'booking',
                'name' => 'Booking & Appointments',
                'category' => 'Scheduling',
                'description' => 'Service scheduling, staff calendars, online client appointment booking, SMS reminders, and deposits.',
                'icon' => 'calendar',
                'monthly_price_kes' => 3200.00,
            ],
            [
                'slug' => 'stock-inventory',
                'name' => 'Stock & Inventory',
                'category' => 'Warehouse & Retail',
                'description' => 'Warehouse inventory, barcodes, low-stock reorder triggers, stock-in/out adjustments, and purchase orders.',
                'icon' => 'package',
                'monthly_price_kes' => 4000.00,
            ],
            [
                'slug' => 'legal-practice',
                'name' => 'Legal Practice Manager',
                'category' => 'Professional Services',
                'description' => 'Advocate case management, court diaries, cause lists, client matters, billable time logs, and disbursements.',
                'icon' => 'scale',
                'monthly_price_kes' => 6500.00,
            ],
            [
                'slug' => 'school-manager',
                'name' => 'School & Academy Manager',
                'category' => 'Education',
                'description' => 'Student registration, CBC grade tracking, fee collections, attendance registers, and academic reports.',
                'icon' => 'graduation-cap',
                'monthly_price_kes' => 7500.00,
            ],
            [
                'slug' => 'clinic-manager',
                'name' => 'Clinic & OPD Manager',
                'category' => 'Healthcare',
                'description' => 'Outpatient queue triage, patient medical records, clinical consultations, vitals, prescriptions, and billing.',
                'icon' => 'stethoscope',
                'monthly_price_kes' => 6500.00,
            ],
            [
                'slug' => 'team',
                'name' => 'Team & Workforce',
                'category' => 'Human Resources',
                'description' => 'Employee directory, departmental organization, attendance, payroll calculations and leave requests.',
                'icon' => 'user-check',
                'monthly_price_kes' => 2800.00,
            ],
            [
                'slug' => 'contracts',
                'name' => 'Contracts & Agreements',
                'category' => 'Legal & Compliance',
                'description' => 'Service level agreements, commercial leases, vendor contracts, digital acknowledgments, and renewal milestones.',
                'icon' => 'file-check',
                'monthly_price_kes' => 2500.00,
            ],
        ];

        foreach ($apps as $app) {
            DB::table('applications')->updateOrInsert(['slug' => $app['slug']], array_merge($app, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // 2. Default Roles
        $roles = [
            ['name' => 'owner', 'display_name' => 'Organization Owner', 'description' => 'Full administrative, financial and member control', 'is_system' => true],
            ['name' => 'admin', 'display_name' => 'Administrator', 'description' => 'Full operational access to subscribed applications', 'is_system' => true],
            ['name' => 'manager', 'display_name' => 'Operations Manager', 'description' => 'Management access to records and reports', 'is_system' => true],
            ['name' => 'member', 'display_name' => 'Staff Member', 'description' => 'Day-to-day transaction creation and viewing', 'is_system' => true],
            ['name' => 'auditor', 'display_name' => 'Auditor / Accountant', 'description' => 'Read-only access to financial records and audit trails', 'is_system' => true],
        ];

        foreach ($roles as $r) {
            DB::table('roles')->updateOrInsert(['name' => $r['name']], array_merge($r, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // 3. Admin User (Secure random password generated or loaded from environment)
        $user = DB::table('users')->where('email', 'admin@paperglow.co.ke')->first();
        if (!$user) {
            $initialPassword = env('ADMIN_INITIAL_PASSWORD') ?: Str::random(16);
            $userId = DB::table('users')->insertGetId([
                'name' => 'Wanjiku Kamau',
                'email' => 'admin@paperglow.co.ke',
                'email_verified_at' => now(),
                'password' => Hash::make($initialPassword),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            if (isset($this->command)) {
                $this->command->info("[Paperglow] Admin created: admin@paperglow.co.ke");
                $this->command->info("[Paperglow] Initial secure password: {$initialPassword}");
                $this->command->warn("[Paperglow] Please change this password upon first login.");
            }
        } else {
            $userId = $user->id;
        }

        // 4. Primary Organization
        $org = DB::table('organizations')->where('slug', 'paperglow-creative')->first();
        if (!$org) {
            $orgId = DB::table('organizations')->insertGetId([
                'uuid' => (string) Str::uuid(),
                'name' => 'Paperglow Creative Group Ltd',
                'slug' => 'paperglow-creative',
                'billing_email' => 'billing@paperglow.co.ke',
                'phone' => '+254 712 345 678',
                'tax_id' => 'P051289192K',
                'address_line1' => 'Upper Hill Business Park, 4th Floor',
                'city' => 'Nairobi',
                'county_state' => 'Nairobi County',
                'country_code' => 'KE',
                'preferred_currency' => 'KES',
                'plan_tier' => 'enterprise',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $orgId = $org->id;
        }

        // 5. Membership
        $ownerRole = DB::table('roles')->where('name', 'owner')->first();
        DB::table('organization_members')->updateOrInsert(
            ['organization_id' => $orgId, 'user_id' => $userId],
            [
                'role_id' => $ownerRole ? $ownerRole->id : null,
                'role_name' => 'owner',
                'title' => 'Managing Director',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // 6. Subscriptions to all applications for primary org
        foreach ($apps as $app) {
            DB::table('organization_subscriptions')->updateOrInsert(
                ['organization_id' => $orgId, 'app_slug' => $app['slug']],
                [
                    'plan_slug' => 'professional',
                    'status' => 'active',
                    'price_kes' => $app['monthly_price_kes'],
                    'current_period_start' => now(),
                    'current_period_end' => now()->addDays(30),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // 7. Seed Initial Records for Applications
        // Business Manager: Customers
        $c1 = DB::table('bm_customers')->insertGetId([
            'organization_id' => $orgId,
            'name' => 'Safaris & Expeditions Africa Ltd',
            'company' => 'Safaris Africa',
            'email' => 'accounts@safarisafrica.co.ke',
            'phone' => '+254 722 110 099',
            'address' => 'Lenana Road, Kilimani',
            'city' => 'Nairobi',
            'kra_pin' => 'P051099231M',
            'outstanding_balance' => 0.00,
            'total_spent' => 245000.00,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $c2 = DB::table('bm_customers')->insertGetId([
            'organization_id' => $orgId,
            'name' => 'Boma Hospitality Suites',
            'company' => 'Boma Group',
            'email' => 'purchasing@bomagroup.ke',
            'phone' => '+254 733 445 566',
            'address' => 'Waiyaki Way, Westlands',
            'city' => 'Nairobi',
            'kra_pin' => 'P051988221A',
            'outstanding_balance' => 38500.00,
            'total_spent' => 112000.00,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Business Manager: Products
        DB::table('bm_products')->insert([
            [
                'organization_id' => $orgId,
                'name' => 'Corporate Branding Package (Premium)',
                'sku' => 'SRV-BRD-01',
                'type' => 'service',
                'category' => 'Design & Branding',
                'stock_quantity' => 100,
                'min_stock_threshold' => 1,
                'buying_price' => 15000.00,
                'selling_price' => 45000.00,
                'unit' => 'package',
                'description' => 'Brand guidelines, vector identity, stationery and digital assets',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'organization_id' => $orgId,
                'name' => 'Embroidered Polo Shirts (Cotton)',
                'sku' => 'MRCH-POLO-02',
                'type' => 'product',
                'category' => 'Apparel Merchandise',
                'stock_quantity' => 250,
                'min_stock_threshold' => 30,
                'buying_price' => 850.00,
                'selling_price' => 1650.00,
                'unit' => 'pcs',
                'description' => 'Heavyweight 220gsm pique cotton with dual chest embroidery',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Business Manager: Invoices
        DB::table('bm_sales_documents')->insert([
            'organization_id' => $orgId,
            'customer_id' => $c1,
            'document_number' => 'INV-2026-0089',
            'document_type' => 'invoice',
            'customer_name' => 'Safaris & Expeditions Africa Ltd',
            'customer_email' => 'accounts@safarisafrica.co.ke',
            'customer_phone' => '+254 722 110 099',
            'customer_address' => 'Lenana Road, Kilimani, Nairobi',
            'customer_kra_pin' => 'P051099231M',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'items' => json_encode([
                ['description' => 'Corporate Identity Redesign & Guidelines', 'quantity' => 1, 'unit_price' => 45000, 'total' => 45000],
                ['description' => 'Embroidered Field Polos (50 pcs)', 'quantity' => 50, 'unit_price' => 1650, 'total' => 82500],
            ]),
            'subtotal' => 127500.00,
            'tax_rate' => 16.00,
            'tax_amount' => 20400.00,
            'discount_amount' => 0.00,
            'grand_total' => 147900.00,
            'amount_paid' => 147900.00,
            'status' => 'paid',
            'payment_method' => 'Bank Transfer (KCB)',
            'notes' => 'Thank you for partnering with Paperglow.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Property Manager: Property & Units
        $propId = DB::table('prop_properties')->insertGetId([
            'organization_id' => $orgId,
            'name' => 'Kilimani Heights Executive Residences',
            'property_type' => 'apartment_building',
            'location' => 'Wood Avenue, Kilimani',
            'address' => 'Plot 209/4812, Wood Avenue',
            'county' => 'Nairobi County',
            'total_units' => 24,
            'caretaker_name' => 'Mwangi Kariuki',
            'caretaker_phone' => '+254 720 998 877',
            'amenities' => 'High speed lift, Backup generator, Borehole water, 24/7 CCTV, Rooftop lounge',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $unitId = DB::table('prop_units')->insertGetId([
            'organization_id' => $orgId,
            'property_id' => $propId,
            'unit_number' => 'Unit 4B',
            'floor' => 4,
            'unit_type' => '2 Bedroom Master Ensuite',
            'monthly_rent_kes' => 65000.00,
            'deposit_kes' => 65000.00,
            'status' => 'occupied',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('prop_tenants')->insert([
            'organization_id' => $orgId,
            'property_id' => $propId,
            'unit_id' => $unitId,
            'name' => 'Dr. Andrew Otieno',
            'email' => 'andrew.otieno@gmail.com',
            'phone' => '+254 711 223 344',
            'national_id' => '28491823',
            'emergency_contact' => 'Mary Otieno (+254 722 334 455)',
            'move_in_date' => now()->subMonths(3)->toDateString(),
            'monthly_rent_kes' => 65000.00,
            'balance_kes' => 0.00,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Pharmacy Manager: Medicines
        DB::table('pharm_medicines')->insert([
            [
                'organization_id' => $orgId,
                'name' => 'Augmentin 625mg Tablets',
                'generic_name' => 'Amoxicillin / Clavulanate Potassium',
                'category' => 'Antibiotics',
                'manufacturer' => 'GlaxoSmithKline Kenya',
                'sku_barcode' => 'MED-AUG-625',
                'batch_number' => 'B26K09',
                'unit_of_measure' => 'Pack of 14',
                'quantity_in_stock' => 84,
                'min_stock_level' => 15,
                'buying_price_kes' => 850.00,
                'selling_price_kes' => 1350.00,
                'expiry_date' => now()->addMonths(18)->toDateString(),
                'requires_prescription' => true,
                'shelf_location' => 'Aisle 2 - Shelf B',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'organization_id' => $orgId,
                'name' => 'Panadol Extra 500mg/65mg',
                'generic_name' => 'Paracetamol / Caffeine',
                'category' => 'Analgesics',
                'manufacturer' => 'Haleon East Africa',
                'sku_barcode' => 'MED-PAN-EXT',
                'batch_number' => 'PAN-4412',
                'unit_of_measure' => 'Pack of 24',
                'quantity_in_stock' => 150,
                'min_stock_level' => 30,
                'buying_price_kes' => 180.00,
                'selling_price_kes' => 300.00,
                'expiry_date' => now()->addMonths(24)->toDateString(),
                'requires_prescription' => false,
                'shelf_location' => 'Front Counter A-1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Chama Manager: Group & Members
        $groupId = DB::table('chama_groups')->insertGetId([
            'organization_id' => $orgId,
            'name' => 'Ushirika Women & Youth Empowerment Chama',
            'registration_number' => 'DSD/NRB/CH/2024/914',
            'cycle_frequency' => 'monthly',
            'monthly_contribution_kes' => 5000.00,
            'welfare_monthly_kes' => 500.00,
            'loan_interest_rate_percent' => 10.00,
            'total_group_savings_kes' => 450000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $m1 = DB::table('chama_members')->insertGetId([
            'organization_id' => $orgId,
            'group_id' => $groupId,
            'membership_number' => 'USH-001',
            'name' => 'Grace Wanjala',
            'national_id' => '24190812',
            'phone' => '+254 721 889 900',
            'email' => 'grace.wanjala@yahoo.com',
            'role_in_chama' => 'Chairperson',
            'total_contributions_kes' => 60000.00,
            'total_loans_taken_kes' => 20000.00,
            'outstanding_loan_balance_kes' => 0.00,
            'status' => 'Active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $m2 = DB::table('chama_members')->insertGetId([
            'organization_id' => $orgId,
            'group_id' => $groupId,
            'membership_number' => 'USH-002',
            'name' => 'Beatrice Muthoni',
            'national_id' => '27182903',
            'phone' => '+254 733 990 011',
            'email' => 'bmuthoni@gmail.com',
            'role_in_chama' => 'Treasurer',
            'total_contributions_kes' => 60000.00,
            'total_loans_taken_kes' => 0.00,
            'outstanding_loan_balance_kes' => 0.00,
            'status' => 'Active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Ticketing: Tickets
        DB::table('tck_tickets')->insert([
            [
                'organization_id' => $orgId,
                'ticket_code' => 'TCK-2026-1049',
                'subject' => 'Branding Merchandise Proof Approval',
                'description' => 'Kindly review and approve the digital vector mockups for Safaris Africa polos before production run.',
                'customer_name' => 'James Maina',
                'customer_email' => 'jmaina@safarisafrica.co.ke',
                'category' => 'Production Approval',
                'priority' => 'high',
                'status' => 'open',
                'assigned_staff_name' => 'Wanjiku Kamau',
                'due_date' => now()->addHours(24),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'organization_id' => $orgId,
                'ticket_code' => 'TCK-2026-1050',
                'subject' => 'DirectAdmin SSL & Domain Setup Inquiry',
                'description' => 'Assistance needed verifying Let\'s Encrypt SSL certificate provisioning on Shujaa Host.',
                'customer_name' => 'Kevin Ndung\'u',
                'customer_email' => 'kevin@bomagroup.ke',
                'category' => 'Hosting Support',
                'priority' => 'medium',
                'status' => 'in_progress',
                'assigned_staff_name' => 'Wanjiku Kamau',
                'due_date' => now()->addHours(48),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Booking: Services & Bookings
        $srvId = DB::table('bk_services')->insertGetId([
            'organization_id' => $orgId,
            'name' => 'Brand Strategy & Identity Consultation',
            'duration_minutes' => 90,
            'price_kes' => 7500.00,
            'description' => 'Comprehensive 90-minute strategy session with creative director and brand strategist',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('bk_bookings')->insert([
            'organization_id' => $orgId,
            'booking_code' => 'BK-9912',
            'customer_name' => 'Sarah Cherono',
            'customer_phone' => '+254 722 778 899',
            'customer_email' => 'sarah.cherono@ventures.ke',
            'service_name' => 'Brand Strategy & Identity Consultation',
            'staff_name' => 'Wanjiku Kamau',
            'booking_date' => now()->addDays(2)->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:30',
            'price_kes' => 7500.00,
            'deposit_kes' => 2500.00,
            'status' => 'confirmed',
            'payment_status' => 'deposit_paid',
            'notes' => 'Client preparing new fintech launch in Q4.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Notifications
        DB::table('notifications')->insert([
            [
                'organization_id' => $orgId,
                'user_id' => $userId,
                'type' => 'system_welcome',
                'title' => 'Paperglow SaaS Provisioned Successfully',
                'message' => 'Your multi-tenant workspace is live with PHP 8.3 & MariaDB persistence on Shujaa Host DirectAdmin.',
                'category' => 'system',
                'action_url' => '/dashboard',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'organization_id' => $orgId,
                'user_id' => $userId,
                'type' => 'invoice_created',
                'title' => 'Invoice INV-2026-0089 Paid in Full',
                'message' => 'Payment of KES 147,900 received from Safaris & Expeditions Africa Ltd.',
                'category' => 'finance',
                'action_url' => '/apps/business-manager',
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ],
        ]);

        // Audit Logs
        DB::table('audit_logs')->insert([
            [
                'organization_id' => $orgId,
                'user_id' => $userId,
                'action' => 'ORGANIZATION_PROVISIONED',
                'module' => 'System',
                'details' => 'Paperglow Creative Group Ltd initialized with 14 active applications.',
                'ip_address' => '127.0.0.1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'organization_id' => $orgId,
                'user_id' => $userId,
                'action' => 'DATABASE_MIGRATION_COMPLETED',
                'module' => 'DirectAdmin MariaDB',
                'details' => 'Migrated from Node/React to Laravel 11 Blade & Livewire SaaS architecture.',
                'ip_address' => '127.0.0.1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
