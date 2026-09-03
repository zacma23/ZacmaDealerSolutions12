<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // if linked to authenticated portal user
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('company')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('alternative_phone')->nullable();
            $table->string('contact_type')->default('lead'); // lead, prospect, customer, seller, partner, vendor, company, other
            $table->string('status')->default('lead'); // lead, prospect, customer, vip, inactive, lost
            $table->string('source')->default('website'); // website, marketplace, listing_inquiry, phone, email, sms, social_media, referral, advertisement, manual_entry, api, ai
            
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->default('Ethiopia');
            
            $table->text('notes')->nullable();
            $table->text('ai_summary')->nullable();
            $table->timestamp('last_contact_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->json('custom_fields')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'contact_type']);
            $table->index(['organization_id', 'email']);
            $table->index(['organization_id', 'phone']);
            $table->index(['organization_id', 'assigned_user_id']);
        });

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained('contacts')->cascadeOnDelete();
            $table->foreignId('listing_id')->nullable()->constrained('listings')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->string('title')->nullable();
            $table->string('source')->default('marketplace');
            $table->string('status')->default('new'); // new, contacted, qualified, proposal, negotiation, won, lost, closed
            $table->string('priority')->default('medium'); // low, medium, high, urgent
            
            $table->decimal('estimated_value', 14, 2)->default(0.00);
            $table->string('currency', 3)->default('ETB');
            
            // AI Scoring
            $table->unsignedSmallInteger('score')->default(50); // 0 - 100
            $table->string('score_category')->default('warm'); // hot, warm, cold
            $table->text('score_explanation')->nullable();
            
            $table->timestamp('last_contact_at')->nullable();
            $table->timestamp('next_follow_up_at')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status', 'priority']);
            $table->index(['organization_id', 'assigned_user_id', 'status']);
            $table->index(['organization_id', 'score_category']);
            $table->index(['organization_id', 'next_follow_up_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
        Schema::dropIfExists('contacts');
    }
};
