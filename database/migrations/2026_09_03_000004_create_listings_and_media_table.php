<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // owner / seller / agent
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->longText('description')->nullable();
            $table->decimal('price', 14, 2)->default(0.00);
            $table->string('currency', 3)->default('ETB');
            $table->string('price_type')->default('fixed'); // fixed, negotiable, contact_price, free, auction
            $table->string('status')->default('draft'); // draft, pending_review, approved, published, reserved, sold, rented, closed, rejected, archived
            $table->string('approval_status')->default('approved'); // pending, approved, rejected
            $table->boolean('featured')->default(false);
            $table->timestamp('featured_until')->nullable();
            
            // Location
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('Ethiopia');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            
            // Contact info
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_whatsapp')->nullable();
            
            // Analytics & Metadata
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('inquiries_count')->default(0);
            $table->json('metadata')->nullable(); // cached dynamic field summary for quick card renders
            
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'slug']);
            $table->index(['organization_id', 'status', 'featured']);
            $table->index(['category_id', 'status', 'price']);
            $table->index(['city', 'status']);
        });

        Schema::create('listing_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('listings')->cascadeOnDelete();
            $table->foreignId('category_field_id')->constrained('category_fields')->cascadeOnDelete();
            $table->text('value')->nullable(); // stored as string or JSON for multi-select
            $table->decimal('numeric_value', 14, 4)->nullable(); // for fast range filters (year, mileage, price, bedrooms, sqm)
            $table->timestamps();

            $table->unique(['listing_id', 'category_field_id']);
            $table->index(['category_field_id', 'numeric_value']);
        });

        Schema::create('listing_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('listings')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_filename')->nullable();
            $table->string('media_type')->default('image'); // image, document, video
            $table->unsignedInteger('file_size_kb')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['listing_id', 'is_primary', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_media');
        Schema::dropIfExists('listing_field_values');
        Schema::dropIfExists('listings');
    }
};
