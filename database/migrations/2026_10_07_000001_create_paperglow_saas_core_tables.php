<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 0. Core Users
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('avatar_url', 2048)->nullable();
            $table->boolean('two_factor_enabled')->default(false);
            $table->string('status', 30)->default('active');
            $table->foreignId('default_organization_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // 1. Organizations / Workspaces
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('billing_email');
            $table->string('phone')->nullable();
            $table->string('tax_id')->nullable(); // KRA PIN
            $table->string('address_line1')->nullable();
            $table->string('city')->default('Nairobi');
            $table->string('county_state')->default('Nairobi County');
            $table->string('country_code', 2)->default('KE');
            $table->string('preferred_currency', 3)->default('KES');
            $table->enum('plan_tier', ['free_trial', 'starter', 'business', 'enterprise'])->default('free_trial');
            $table->enum('status', ['active', 'past_due', 'suspended'])->default('active');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();
        });

        // 2. Roles & Permissions
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 50);
            $table->string('display_name', 100);
            $table->string('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('organization_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->nullable()->constrained()->nullOnDelete();
            $table->string('role_name', 50)->default('member');
            $table->string('title')->nullable();
            $table->enum('status', ['active', 'invited', 'suspended'])->default('active');
            $table->timestamps();
            $table->unique(['organization_id', 'user_id']);
        });

        // 3. SaaS Applications Catalog & Organization Subscriptions
        Schema::create('applications', function (Blueprint $table) {
            $table->string('slug', 80)->primary(); // e.g. 'paperglow-business-manager'
            $table->string('name', 120);
            $table->string('category', 60);
            $table->text('description')->nullable();
            $table->string('icon', 60)->default('briefcase');
            $table->decimal('monthly_price_kes', 12, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('organization_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('app_slug', 80);
            $table->string('plan_slug', 50)->default('professional');
            $table->enum('status', ['active', 'trial', 'past_due', 'canceled'])->default('active');
            $table->decimal('price_kes', 12, 2)->default(0.00);
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamps();
            $table->foreign('app_slug')->references('slug')->on('applications')->cascadeOnDelete();
            $table->unique(['organization_id', 'app_slug']);
        });

        // 4. Shared SaaS Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 100);
            $table->string('title', 255);
            $table->text('message');
            $table->string('category', 50)->default('general');
            $table->string('action_url', 500)->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['organization_id', 'user_id', 'read_at']);
        });

        // 5. Shared SaaS Audit Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 100);
            $table->string('module', 60);
            $table->text('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->index(['organization_id', 'module', 'created_at']);
        });

        // 6. Shared File Attachments
        Schema::create('document_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('related_module', 60);
            $table->string('related_id', 100);
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type', 100)->nullable();
            $table->bigInteger('file_size_bytes')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_attachments');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('organization_subscriptions');
        Schema::dropIfExists('applications');
        Schema::dropIfExists('organization_members');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('organizations');
        Schema::dropIfExists('users');
    }
};
