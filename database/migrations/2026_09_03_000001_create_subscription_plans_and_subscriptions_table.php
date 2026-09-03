<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('price', 10, 2)->default(0.00);
            $table->string('currency', 3)->default('USD');
            $table->string('interval')->default('monthly'); // monthly, yearly
            $table->unsignedInteger('listing_limit')->default(100);
            $table->unsignedInteger('contact_limit')->default(100);
            $table->unsignedInteger('user_limit')->default(1);
            $table->unsignedInteger('ai_request_limit')->default(50);
            $table->unsignedInteger('storage_limit_mb')->default(500);
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('subdomain')->nullable()->unique();
            $table->string('custom_domain')->nullable()->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->default('Ethiopia');
            $table->string('currency', 3)->default('ETB');
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->json('branding_colors')->nullable(); // primary, secondary, accent
            $table->string('status')->default('active'); // active, suspended, pending
            $table->timestamp('subscription_ends_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'slug']);
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained('subscription_plans');
            $table->string('status')->default('active'); // active, past_due, cancelled, expired
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_starts_at')->nullable();
            $table->timestamp('current_period_ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('organizations');
        Schema::dropIfExists('subscription_plans');
    }
};
