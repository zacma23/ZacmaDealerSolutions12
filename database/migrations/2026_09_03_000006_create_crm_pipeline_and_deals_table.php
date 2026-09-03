<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deal_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('name'); // New, Contacted, Qualified, Proposal, Negotiation, Won, Lost, Closed
            $table->string('slug');
            $table->unsignedSmallInteger('win_probability')->default(20); // 0 - 100%
            $table->string('color')->default('#3B82F6');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_closed_won')->default(false);
            $table->boolean('is_closed_lost')->default(false);
            $table->timestamps();

            $table->unique(['organization_id', 'slug']);
            $table->index(['organization_id', 'sort_order']);
        });

        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained('contacts')->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->foreignId('listing_id')->nullable()->constrained('listings')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('deal_stage_id')->constrained('deal_stages')->cascadeOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->string('title');
            $table->decimal('value', 14, 2)->default(0.00);
            $table->string('currency', 3)->default('ETB');
            $table->unsignedSmallInteger('probability')->default(50);
            $table->date('expected_close_date')->nullable();
            $table->string('source')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'deal_stage_id']);
            $table->index(['organization_id', 'assigned_user_id']);
            $table->index(['organization_id', 'expected_close_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deals');
        Schema::dropIfExists('deal_stages');
    }
};
