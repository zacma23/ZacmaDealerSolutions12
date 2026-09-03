<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // customer
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('listing_id')->nullable()->constrained('listings')->nullOnDelete();
            
            $table->string('order_number')->unique();
            $table->string('purchase_type')->default('product'); // product, vehicle_deposit, property_reserve, service, featured_listing, subscription
            $table->decimal('total_amount', 14, 2);
            $table->string('currency', 3)->default('ETB');
            
            $table->string('status')->default('pending'); // pending, processing, confirmed, completed, cancelled, refunded
            $table->string('payment_status')->default('unpaid'); // unpaid, partial, paid, refunded
            
            // Customer info snapshot
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'payment_status']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('listing_id')->nullable()->constrained('listings')->nullOnDelete();
            $table->string('item_name');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 14, 2);
            $table->decimal('total_price', 14, 2);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->string('provider'); // santimpay, telebirr, chapa, paypal, card, cash
            $table->decimal('amount', 14, 2);
            $table->string('currency', 3)->default('ETB');
            
            $table->string('transaction_reference')->unique(); // our local internal unique ref
            $table->string('provider_reference')->nullable(); // ref returned by gateway
            $table->string('status')->default('pending'); // pending, verified, failed, refunded
            
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'provider', 'status']);
            $table->index(['transaction_reference']);
            $table->index(['provider_reference']);
        });

        Schema::create('payment_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('event_type')->nullable();
            $table->json('payload');
            $table->boolean('is_processed')->default(false);
            $table->text('error_log')->nullable();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->string('invoice_number')->unique();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 14, 2);
            $table->decimal('tax', 14, 2)->default(0.00);
            $table->decimal('total', 14, 2);
            $table->string('currency', 3)->default('ETB');
            $table->string('status')->default('paid'); // unpaid, paid, cancelled
            $table->timestamps();

            $table->index(['organization_id', 'invoice_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('payment_webhooks');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
