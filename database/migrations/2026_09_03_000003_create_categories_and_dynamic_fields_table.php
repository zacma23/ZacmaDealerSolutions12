<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('icon')->nullable(); // icon class or SVG name
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('allowed_purchase_types')->nullable(); // contact, quote, reserve, buy_now, deposit, book
            $table->timestamps();

            $table->unique(['organization_id', 'slug']);
            $table->index(['organization_id', 'is_active', 'sort_order']);
        });

        Schema::create('category_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('name'); // programmatic key, e.g., 'fuel_type', 'bedrooms', 'storage'
            $table->string('label'); // display label, e.g., 'Fuel Type', 'Bedrooms'
            $table->string('field_type')->default('text'); // text, number, select, multiselect, date, boolean, textarea, currency, file
            $table->string('placeholder')->nullable();
            $table->string('default_value')->nullable();
            $table->string('unit')->nullable(); // e.g. km, sq.m, GB, HP
            $table->boolean('is_required')->default(false);
            $table->boolean('is_filterable')->default(true);
            $table->boolean('show_in_card')->default(false); // highlight in card view
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['category_id', 'name']);
            $table->index(['category_id', 'is_filterable', 'sort_order']);
        });

        Schema::create('category_field_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_field_id')->constrained('category_fields')->cascadeOnDelete();
            $table->string('label');
            $table->string('value');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['category_field_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_field_options');
        Schema::dropIfExists('category_fields');
        Schema::dropIfExists('categories');
    }
};
