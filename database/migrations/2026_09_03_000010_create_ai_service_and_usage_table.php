<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('feature'); // crm_assistant, lead_scoring, customer_summary, draft_email, draft_sms, listing_description, marketplace_search
            $table->string('provider')->default('gemini'); // gemini, openai, anthropic, mock
            $table->string('model')->default('gemini-1.5-flash');
            $table->unsignedInteger('prompt_tokens')->default(0);
            $table->unsignedInteger('completion_tokens')->default(0);
            $table->unsignedInteger('total_tokens')->default(0);
            $table->decimal('estimated_cost', 10, 6)->default(0.000000);
            $table->string('status')->default('success'); // success, failed, throttled
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'feature']);
            $table->index(['organization_id', 'created_at']);
        });

        Schema::create('ai_usage_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('year_month', 7); // e.g. '2026-09'
            $table->unsignedBigInteger('total_requests')->default(0);
            $table->unsignedBigInteger('total_tokens')->default(0);
            $table->decimal('total_cost', 12, 4)->default(0.0000);
            $table->timestamps();

            $table->unique(['organization_id', 'year_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage_summaries');
        Schema::dropIfExists('ai_requests');
    }
};
