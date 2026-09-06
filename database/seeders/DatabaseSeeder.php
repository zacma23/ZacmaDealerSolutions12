<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Category;
use App\Models\CategoryField;
use App\Models\CategoryFieldOption;
use App\Models\Contact;
use App\Models\CrmActivity;
use App\Models\Deal;
use App\Models\DealStage;
use App\Models\Lead;
use App\Models\Listing;
use App\Models\ListingFieldValue;
use App\Models\ListingMedia;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Subscription Plans for Dealers & Businesses
        $basicPlan = SubscriptionPlan::create([
            'name' => 'Dealer Basic',
            'slug' => 'dealer-basic',
            'price' => 5000.00,
            'currency' => 'ETB',
            'interval' => 'monthly',
            'listing_limit' => 150,
            'contact_limit' => 500,
            'user_limit' => 2,
            'ai_request_limit' => 200,
            'storage_limit_mb' => 2048,
            'features' => [
                'posts_per_day' => 5,
                'customer_phone_access' => false,
                'ai_customer_assistant' => false,
                'ai_username_branding' => false,
                'ai_vision_autofill' => true,
                'ai_lead_scoring' => true,
                'crm_pipeline' => true,
                'whatsapp_direct' => false,
                'export_contacts_csv' => false,
                'description' => 'Ideal for boutique car dealers, electronics shops, and local property agents.',
            ],
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $premiumPlan = SubscriptionPlan::create([
            'name' => 'Dealer Premium',
            'slug' => 'dealer-premium',
            'price' => 8000.00,
            'currency' => 'ETB',
            'interval' => 'monthly',
            'listing_limit' => 600,
            'contact_limit' => 2500,
            'user_limit' => 5,
            'ai_request_limit' => 1000,
            'storage_limit_mb' => 10240,
            'features' => [
                'posts_per_day' => 20,
                'customer_phone_access' => true,
                'ai_customer_assistant' => true,
                'ai_username_branding' => false,
                'ai_vision_autofill' => true,
                'ai_lead_scoring' => true,
                'crm_pipeline' => true,
                'whatsapp_direct' => true,
                'export_contacts_csv' => false,
                'description' => 'Designed for growing vehicle dealerships, electronics retailers, and real estate agencies.',
            ],
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $advancePlan = SubscriptionPlan::create([
            'name' => 'Dealer Advance',
            'slug' => 'dealer-advance',
            'price' => 12000.00,
            'currency' => 'ETB',
            'interval' => 'monthly',
            'listing_limit' => 99999,
            'contact_limit' => 99999,
            'user_limit' => 99999,
            'ai_request_limit' => 5000,
            'storage_limit_mb' => 51200,
            'features' => [
                'posts_per_day' => -1, // Unlimited
                'customer_phone_access' => true,
                'ai_customer_assistant' => true,
                'ai_username_branding' => true,
                'ai_vision_autofill' => true,
                'ai_lead_scoring' => true,
                'crm_pipeline' => true,
                'whatsapp_direct' => true,
                'export_contacts_csv' => true,
                'priority_marketplace' => true,
                'description' => 'Enterprise package for premier automotive groups, real estate developers, and commercial distributors.',
            ],
            'is_active' => true,
            'sort_order' => 3,
        ]);

        // 2. Global Super Admin User
        $superAdmin = User::create([
            'name' => 'System Super Admin',
            'email' => 'admin@zacma.com',
            'phone' => '+251911000001',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        // 3. Demo Organizations
        // Organization 1: Zacma Auto (Dealership)
        $autoOrg = Organization::create([
            'name' => 'Zacma Auto Dealership',
            'slug' => 'zacma-auto',
            'subdomain' => 'auto',
            'email' => 'auto@zacma.com',
            'phone' => '+251911123456',
            'city' => 'Addis Ababa',
            'country' => 'Ethiopia',
            'currency' => 'ETB',
            'subscription_plan_id' => $premiumPlan->id,
            'status' => 'active',
            'subscription_ends_at' => now()->addYear(),
            'branding_colors' => ['primary' => '#2563EB'],
        ]);

        // Organization 2: Zacma Property (Real Estate)
        $propertyOrg = Organization::create([
            'name' => 'Zacma Prime Real Estate',
            'slug' => 'zacma-property',
            'subdomain' => 'property',
            'email' => 'property@zacma.com',
            'phone' => '+251911654321',
            'city' => 'Addis Ababa',
            'country' => 'Ethiopia',
            'currency' => 'ETB',
            'subscription_plan_id' => $advancePlan->id,
            'status' => 'active',
            'subscription_ends_at' => now()->addYear(),
            'branding_colors' => ['primary' => '#059669'],
        ]);

        // Organization 3: Zacma Electronics (Retail & Devices)
        $electronicsOrg = Organization::create([
            'name' => 'Zacma Electronics Hub',
            'slug' => 'zacma-electronics',
            'subdomain' => 'electronics',
            'email' => 'electronics@zacma.com',
            'phone' => '+251911777888',
            'city' => 'Addis Ababa',
            'country' => 'Ethiopia',
            'currency' => 'ETB',
            'subscription_plan_id' => $basicPlan->id,
            'status' => 'active',
            'subscription_ends_at' => now()->addYear(),
            'branding_colors' => ['primary' => '#7C3AED'],
        ]);

        // 4. Organization Users
        $autoAdmin = User::create([
            'organization_id' => $autoOrg->id,
            'name' => 'Abebe Bikila (Auto GM)',
            'email' => 'auto@zacma.com',
            'phone' => '+251911123457',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ORGANIZATION_ADMIN,
            'is_active' => true,
        ]);

        $autoAgent = User::create([
            'organization_id' => $autoOrg->id,
            'name' => 'Dawit Kebede (Sales Agent)',
            'email' => 'dawit@zacma.com',
            'phone' => '+251911123458',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SALES_AGENT,
            'is_active' => true,
        ]);

        $propAdmin = User::create([
            'organization_id' => $propertyOrg->id,
            'name' => 'Sara Tadesse (Property Director)',
            'email' => 'property@zacma.com',
            'phone' => '+251911654322',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ORGANIZATION_ADMIN,
            'is_active' => true,
        ]);

        $elecAdmin = User::create([
            'organization_id' => $electronicsOrg->id,
            'name' => 'Yonatan Girma (Electronics GM)',
            'email' => 'electronics@zacma.com',
            'phone' => '+251911777889',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ORGANIZATION_ADMIN,
            'is_active' => true,
        ]);

        $customer = User::create([
            'organization_id' => $autoOrg->id,
            'name' => 'Tewodros Kassahun (Customer)',
            'email' => 'customer@zacma.com',
            'phone' => '+251922334455',
            'password' => Hash::make('password'),
            'role' => User::ROLE_CUSTOMER,
            'is_active' => true,
        ]);

        // 5. Deal Stages for each organization
        $stagesConfig = [
            ['name' => 'New Lead', 'slug' => 'new', 'win_probability' => 10, 'color' => '#3B82F6', 'sort_order' => 1],
            ['name' => 'Contacted', 'slug' => 'contacted', 'win_probability' => 25, 'color' => '#8B5CF6', 'sort_order' => 2],
            ['name' => 'Qualified', 'slug' => 'qualified', 'win_probability' => 50, 'color' => '#EAB308', 'sort_order' => 3],
            ['name' => 'Proposal', 'slug' => 'proposal', 'win_probability' => 70, 'color' => '#F97316', 'sort_order' => 4],
            ['name' => 'Negotiation', 'slug' => 'negotiation', 'win_probability' => 85, 'color' => '#EC4899', 'sort_order' => 5],
            ['name' => 'Closed Won', 'slug' => 'won', 'win_probability' => 100, 'color' => '#10B981', 'sort_order' => 6, 'is_closed_won' => true],
            ['name' => 'Closed Lost', 'slug' => 'lost', 'win_probability' => 0, 'color' => '#EF4444', 'sort_order' => 7, 'is_closed_lost' => true],
        ];

        $autoStages = [];
        foreach ($stagesConfig as $sc) {
            $autoStages[$sc['slug']] = $autoOrg->dealStages()->create($sc);
            $propertyOrg->dealStages()->create($sc);
            $electronicsOrg->dealStages()->create($sc);
        }

        // 6. Dynamic Categories & Custom Fields
        // Category 1: Vehicles
        $vehiclesCat = Category::create([
            'name' => 'Vehicles & Automotive',
            'slug' => 'vehicles',
            'icon' => 'fa-solid fa-car',
            'description' => 'Sedans, SUVs, Trucks, Commercial vehicles and Motorcycles',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $vBrand = $vehiclesCat->fields()->create(['name' => 'brand', 'label' => 'Brand / Make', 'field_type' => 'text', 'is_required' => true, 'is_filterable' => true, 'show_in_card' => true, 'sort_order' => 1]);
        $vModel = $vehiclesCat->fields()->create(['name' => 'model', 'label' => 'Model', 'field_type' => 'text', 'is_required' => true, 'is_filterable' => true, 'show_in_card' => true, 'sort_order' => 2]);
        $vYear = $vehiclesCat->fields()->create(['name' => 'year', 'label' => 'Year of Manufacture', 'field_type' => 'number', 'is_required' => true, 'is_filterable' => true, 'show_in_card' => true, 'sort_order' => 3]);
        $vMileage = $vehiclesCat->fields()->create(['name' => 'mileage', 'label' => 'Mileage', 'field_type' => 'number', 'unit' => 'km', 'is_filterable' => true, 'show_in_card' => true, 'sort_order' => 4]);
        $vTransmission = $vehiclesCat->fields()->create(['name' => 'transmission', 'label' => 'Transmission', 'field_type' => 'select', 'is_filterable' => true, 'sort_order' => 5]);
        $vTransmission->options()->createMany([
            ['label' => 'Automatic', 'value' => 'automatic', 'sort_order' => 1],
            ['label' => 'Manual', 'value' => 'manual', 'sort_order' => 2],
        ]);
        $vFuel = $vehiclesCat->fields()->create(['name' => 'fuel_type', 'label' => 'Fuel Type', 'field_type' => 'select', 'is_filterable' => true, 'sort_order' => 6]);
        $vFuel->options()->createMany([
            ['label' => 'Petrol', 'value' => 'petrol', 'sort_order' => 1],
            ['label' => 'Diesel', 'value' => 'diesel', 'sort_order' => 2],
            ['label' => 'Hybrid', 'value' => 'hybrid', 'sort_order' => 3],
            ['label' => 'Electric', 'value' => 'electric', 'sort_order' => 4],
        ]);

        // Category 2: Real Estate / Property
        $propertyCat = Category::create([
            'name' => 'Real Estate & Properties',
            'slug' => 'property',
            'icon' => 'fa-solid fa-house-chimney',
            'description' => 'Apartments, Villas, Commercial properties, and Land',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $pBedrooms = $propertyCat->fields()->create(['name' => 'bedrooms', 'label' => 'Bedrooms', 'field_type' => 'number', 'is_filterable' => true, 'show_in_card' => true, 'sort_order' => 1]);
        $pBathrooms = $propertyCat->fields()->create(['name' => 'bathrooms', 'label' => 'Bathrooms', 'field_type' => 'number', 'is_filterable' => true, 'sort_order' => 2]);
        $pArea = $propertyCat->fields()->create(['name' => 'area_sqm', 'label' => 'Total Area', 'field_type' => 'number', 'unit' => 'sq.m', 'is_filterable' => true, 'show_in_card' => true, 'sort_order' => 3]);
        $pFurnished = $propertyCat->fields()->create(['name' => 'furnished', 'label' => 'Furnished', 'field_type' => 'select', 'is_filterable' => true, 'sort_order' => 4]);
        $pFurnished->options()->createMany([
            ['label' => 'Fully Furnished', 'value' => 'fully_furnished', 'sort_order' => 1],
            ['label' => 'Semi-Furnished', 'value' => 'semi_furnished', 'sort_order' => 2],
            ['label' => 'Unfurnished', 'value' => 'unfurnished', 'sort_order' => 3],
        ]);

        // Category 3: Electronics
        $electronicsCat = Category::create([
            'name' => 'Electronics & Devices',
            'slug' => 'electronics',
            'icon' => 'fa-solid fa-laptop',
            'description' => 'Smartphones, Laptops, Computers, and Accessories',
            'is_active' => true,
            'sort_order' => 3,
        ]);

        $eType = $electronicsCat->fields()->create(['name' => 'device_type', 'label' => 'Device Type', 'field_type' => 'select', 'is_filterable' => true, 'sort_order' => 1]);
        $eType->options()->createMany([
            ['label' => 'Smartphone', 'value' => 'smartphone', 'sort_order' => 1],
            ['label' => 'Laptop', 'value' => 'laptop', 'sort_order' => 2],
            ['label' => 'Tablet', 'value' => 'tablet', 'sort_order' => 3],
        ]);
        $eStorage = $electronicsCat->fields()->create(['name' => 'storage', 'label' => 'Storage Capacity', 'field_type' => 'text', 'unit' => 'GB', 'is_filterable' => true, 'show_in_card' => true, 'sort_order' => 2]);
        $eRam = $electronicsCat->fields()->create(['name' => 'ram', 'label' => 'RAM Memory', 'field_type' => 'text', 'unit' => 'GB', 'is_filterable' => true, 'show_in_card' => true, 'sort_order' => 3]);

        // Category 4 & 5: Machinery & Services
        $machineryCat = Category::create([
            'name' => 'Heavy Machinery & Agriculture',
            'slug' => 'machinery',
            'icon' => 'fa-solid fa-tractor',
            'description' => 'Excavators, Tractors, Commercial generators and Industrial equipment',
            'is_active' => true,
            'sort_order' => 4,
        ]);

        $servicesCat = Category::create([
            'name' => 'Professional Services',
            'slug' => 'services',
            'icon' => 'fa-solid fa-briefcase',
            'description' => 'Consulting, Legal, Architecture, and Maintenance services',
            'is_active' => true,
            'sort_order' => 5,
        ]);

        // 7. Demo Listings
        // Vehicle Listing 1
        $car1 = Listing::create([
            'organization_id' => $autoOrg->id,
            'user_id' => $autoAdmin->id,
            'category_id' => $vehiclesCat->id,
            'title' => '2023 Toyota Land Cruiser VXR 3.3L Twin Turbo',
            'slug' => '2023-toyota-land-cruiser-vxr-twin-turbo',
            'description' => "Flagship luxury SUV with exceptional off-road performance. Zero accidents, agency maintained, full leather interior, heads-up display, 360-degree cameras, and adaptive air suspension.",
            'price' => 18500000,
            'currency' => 'ETB',
            'price_type' => 'fixed',
            'city' => 'Addis Ababa',
            'address' => 'Bole Medhanialem Showroom',
            'contact_phone' => '+251911123456',
            'contact_email' => 'sales@zacma-auto.com',
            'status' => Listing::STATUS_PUBLISHED,
            'approval_status' => 'approved',
            'featured' => true,
            'views_count' => 342,
            'inquiries_count' => 18,
        ]);

        ListingFieldValue::create(['listing_id' => $car1->id, 'category_field_id' => $vBrand->id, 'value' => 'Toyota']);
        ListingFieldValue::create(['listing_id' => $car1->id, 'category_field_id' => $vModel->id, 'value' => 'Land Cruiser VXR']);
        ListingFieldValue::create(['listing_id' => $car1->id, 'category_field_id' => $vYear->id, 'value' => '2023', 'numeric_value' => 2023]);
        ListingFieldValue::create(['listing_id' => $car1->id, 'category_field_id' => $vMileage->id, 'value' => '12000', 'numeric_value' => 12000]);
        ListingFieldValue::create(['listing_id' => $car1->id, 'category_field_id' => $vTransmission->id, 'value' => 'Automatic']);
        ListingFieldValue::create(['listing_id' => $car1->id, 'category_field_id' => $vFuel->id, 'value' => 'Diesel']);

        // Vehicle Listing 2
        $car2 = Listing::create([
            'organization_id' => $autoOrg->id,
            'user_id' => $autoAdmin->id,
            'category_id' => $vehiclesCat->id,
            'title' => '2024 Hyundai Tucson Executive Hybrid',
            'slug' => '2024-hyundai-tucson-hybrid-executive',
            'description' => 'Brand new hybrid compact SUV, zero mileage, smart safety package, panoramic sunroof, wireless Apple CarPlay.',
            'price' => 7900000,
            'currency' => 'ETB',
            'price_type' => 'negotiable',
            'city' => 'Addis Ababa',
            'address' => 'Sarbet Showroom',
            'status' => Listing::STATUS_PUBLISHED,
            'approval_status' => 'approved',
            'featured' => true,
            'views_count' => 195,
            'inquiries_count' => 9,
        ]);

        ListingFieldValue::create(['listing_id' => $car2->id, 'category_field_id' => $vBrand->id, 'value' => 'Hyundai']);
        ListingFieldValue::create(['listing_id' => $car2->id, 'category_field_id' => $vModel->id, 'value' => 'Tucson']);
        ListingFieldValue::create(['listing_id' => $car2->id, 'category_field_id' => $vYear->id, 'value' => '2024', 'numeric_value' => 2024]);
        ListingFieldValue::create(['listing_id' => $car2->id, 'category_field_id' => $vMileage->id, 'value' => '50', 'numeric_value' => 50]);
        ListingFieldValue::create(['listing_id' => $car2->id, 'category_field_id' => $vTransmission->id, 'value' => 'Automatic']);
        ListingFieldValue::create(['listing_id' => $car2->id, 'category_field_id' => $vFuel->id, 'value' => 'Hybrid']);

        // Property Listing 1
        $house1 = Listing::create([
            'organization_id' => $propertyOrg->id,
            'user_id' => $propAdmin->id,
            'category_id' => $propertyCat->id,
            'title' => 'Luxury 3-Bedroom Penthouse with City View in Bole',
            'slug' => 'luxury-3-bedroom-penthouse-bole-addis',
            'description' => 'Top-floor penthouse apartment with wrap-around terrace, German fitted kitchen, backup generator, 24/7 security, and dedicated underground parking.',
            'price' => 28000000,
            'currency' => 'ETB',
            'price_type' => 'fixed',
            'city' => 'Addis Ababa',
            'address' => 'Bole Atlas, Addis Ababa',
            'status' => Listing::STATUS_PUBLISHED,
            'approval_status' => 'approved',
            'featured' => true,
            'views_count' => 450,
            'inquiries_count' => 24,
        ]);

        ListingFieldValue::create(['listing_id' => $house1->id, 'category_field_id' => $pBedrooms->id, 'value' => '3', 'numeric_value' => 3]);
        ListingFieldValue::create(['listing_id' => $house1->id, 'category_field_id' => $pBathrooms->id, 'value' => '3', 'numeric_value' => 3]);
        ListingFieldValue::create(['listing_id' => $house1->id, 'category_field_id' => $pArea->id, 'value' => '245', 'numeric_value' => 245]);
        ListingFieldValue::create(['listing_id' => $house1->id, 'category_field_id' => $pFurnished->id, 'value' => 'Fully Furnished']);

        // Electronics Listing 1
        $laptop1 = Listing::create([
            'organization_id' => $electronicsOrg->id,
            'user_id' => $elecAdmin->id,
            'category_id' => $electronicsCat->id,
            'title' => 'Apple MacBook Pro 16" M3 Max (36GB RAM, 1TB SSD)',
            'slug' => 'apple-macbook-pro-16-m3-max-36gb',
            'description' => 'Space Black edition, pristine sealed box with 1-year official Apple warranty. Ideal for AI engineering, video editing, and software development.',
            'price' => 395000,
            'currency' => 'ETB',
            'price_type' => 'fixed',
            'city' => 'Addis Ababa',
            'address' => 'Piazza Electronics Mall',
            'status' => Listing::STATUS_PUBLISHED,
            'approval_status' => 'approved',
            'featured' => false,
            'views_count' => 120,
            'inquiries_count' => 6,
        ]);

        ListingFieldValue::create(['listing_id' => $laptop1->id, 'category_field_id' => $eType->id, 'value' => 'Laptop']);
        ListingFieldValue::create(['listing_id' => $laptop1->id, 'category_field_id' => $eStorage->id, 'value' => '1000']);
        ListingFieldValue::create(['listing_id' => $laptop1->id, 'category_field_id' => $eRam->id, 'value' => '36']);

        // Machinery Listing 1
        $machinery1 = Listing::create([
            'organization_id' => $autoOrg->id,
            'user_id' => $autoAdmin->id,
            'category_id' => $machineryCat->id,
            'title' => '2022 Caterpillar 320D Hydraulic Excavator',
            'slug' => '2022-caterpillar-320d-hydraulic-excavator',
            'description' => 'Heavy-duty industrial excavator, 2,800 operating hours, CAT C7.1 ACERT engine, reinforced heavy-duty boom and bucket. Fully inspected and ready for immediate deployment on construction or mining projects.',
            'price' => 14200000,
            'currency' => 'ETB',
            'price_type' => 'negotiable',
            'city' => 'Addis Ababa',
            'address' => 'Kality Heavy Equipment Yard',
            'contact_phone' => '+251911123456',
            'contact_email' => 'machinery@zacma-auto.com',
            'status' => Listing::STATUS_PUBLISHED,
            'approval_status' => 'approved',
            'featured' => true,
            'views_count' => 280,
            'inquiries_count' => 14,
        ]);

        // Services Listing 1
        $service1 = Listing::create([
            'organization_id' => $autoOrg->id,
            'user_id' => $autoAdmin->id,
            'category_id' => $servicesCat->id,
            'title' => 'Certified Fleet Audit & Vehicle Appraisal Services',
            'slug' => 'certified-fleet-audit-and-vehicle-appraisal',
            'description' => 'Comprehensive 150-point technical inspection, commercial vehicle fleet valuation, and mechanical diagnostic report for financial institutions, dealerships, and enterprise fleet managers.',
            'price' => 45000,
            'currency' => 'ETB',
            'price_type' => 'fixed',
            'city' => 'Addis Ababa',
            'address' => 'Bole Sub-city, Addis Ababa',
            'contact_phone' => '+251911123456',
            'contact_email' => 'consulting@zacma.com',
            'status' => Listing::STATUS_PUBLISHED,
            'approval_status' => 'approved',
            'featured' => false,
            'views_count' => 160,
            'inquiries_count' => 8,
        ]);

        // Listing Media (Photos with high-resolution imagery)
        $listingMediaSeed = [
            [$car1->id, 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=1200&q=80', 'land-cruiser-front.jpg', true],
            [$car1->id, 'https://images.unsplash.com/photo-1583121274602-3e2820c69888?auto=format&fit=crop&w=1200&q=80', 'land-cruiser-interior.jpg', false],
            [$car2->id, 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=1200&q=80', 'hyundai-tucson-front.jpg', true],
            [$car2->id, 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1200&q=80', 'hyundai-tucson-side.jpg', false],
            [$house1->id, 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1200&q=80', 'penthouse-exterior.jpg', true],
            [$house1->id, 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80', 'penthouse-living.jpg', false],
            [$laptop1->id, 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=1200&q=80', 'macbook-pro-16.jpg', true],
            [$laptop1->id, 'https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?auto=format&fit=crop&w=1200&q=80', 'macbook-pro-desk.jpg', false],
            [$machinery1->id, 'https://images.unsplash.com/photo-1578575437130-527eed3abbec?auto=format&fit=crop&w=1200&q=80', 'cat-excavator.jpg', true],
            [$service1->id, 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=1200&q=80', 'fleet-audit-service.jpg', true],
        ];

        foreach ($listingMediaSeed as $mediaItem) {
            ListingMedia::create([
                'listing_id' => $mediaItem[0],
                'file_path' => $mediaItem[1],
                'original_filename' => $mediaItem[2],
                'media_type' => 'image',
                'file_size_kb' => 250,
                'is_primary' => $mediaItem[3],
                'sort_order' => 1,
            ]);
        }

        // 8. CRM Contacts & Leads
        // Contact 1
        $contact1 = Contact::create([
            'organization_id' => $autoOrg->id,
            'assigned_user_id' => $autoAgent->id,
            'first_name' => 'Hailu',
            'last_name' => 'Merid',
            'company' => 'Blue Nile Trading',
            'email' => 'hailu.merid@gmail.com',
            'phone' => '+251911445566',
            'contact_type' => Contact::TYPE_CUSTOMER,
            'status' => Contact::STATUS_PROSPECT,
            'source' => 'listing_inquiry',
            'ai_summary' => 'High-net-worth client interested in commercial SUV fleet. Responsive on phone, requested quotation with trade-in.',
            'last_contact_at' => now()->subHours(5),
        ]);

        $lead1 = Lead::create([
            'organization_id' => $autoOrg->id,
            'contact_id' => $contact1->id,
            'listing_id' => $car1->id,
            'category_id' => $vehiclesCat->id,
            'assigned_user_id' => $autoAgent->id,
            'title' => 'Inquiry for Toyota Land Cruiser VXR',
            'source' => 'listing_inquiry',
            'status' => Lead::STATUS_QUALIFIED,
            'priority' => Lead::PRIORITY_URGENT,
            'score' => 88,
            'score_category' => Lead::SCORE_HOT,
            'score_explanation' => 'Calculated score 88/100 (HOT). Customer requested test drive, verified financing, and trade-in inspection.',
            'estimated_value' => 18500000,
            'currency' => 'ETB',
            'last_contact_at' => now()->subHours(5),
            'next_follow_up_at' => now()->addHours(24),
        ]);

        // Pipeline Deal for Contact 1
        $deal1 = Deal::create([
            'organization_id' => $autoOrg->id,
            'contact_id' => $contact1->id,
            'deal_stage_id' => $autoStages['proposal']->id,
            'listing_id' => $car1->id,
            'assigned_user_id' => $autoAgent->id,
            'title' => 'Land Cruiser Purchase Contract',
            'value' => 18500000,
            'currency' => 'ETB',
            'probability' => 70,
            'expected_close_date' => now()->addDays(7),
        ]);

        // Contact 2
        $contact2 = Contact::create([
            'organization_id' => $autoOrg->id,
            'assigned_user_id' => $autoAgent->id,
            'first_name' => 'Bethlehem',
            'last_name' => 'Alemu',
            'email' => 'bethlehem.a@solerebel.com',
            'phone' => '+251911998877',
            'contact_type' => Contact::TYPE_LEAD,
            'status' => Contact::STATUS_LEAD,
            'source' => 'website_chat',
            'ai_summary' => 'Looking for a fuel-efficient hybrid SUV for daily family commute. Comparing Tucson vs RAV4.',
            'last_contact_at' => now()->subDays(1),
        ]);

        $lead2 = Lead::create([
            'organization_id' => $autoOrg->id,
            'contact_id' => $contact2->id,
            'listing_id' => $car2->id,
            'category_id' => $vehiclesCat->id,
            'assigned_user_id' => $autoAgent->id,
            'title' => 'Tucson Hybrid Viewing & Quotation',
            'source' => 'website_chat',
            'status' => Lead::STATUS_CONTACTED,
            'priority' => Lead::PRIORITY_HIGH,
            'score' => 65,
            'score_category' => Lead::SCORE_WARM,
            'score_explanation' => 'Calculated score 65/100 (WARM). Engaged in chat, requested pricing sheet.',
            'estimated_value' => 7900000,
            'currency' => 'ETB',
            'last_contact_at' => now()->subDays(1),
            'next_follow_up_at' => now()->addDays(2),
        ]);

        // 9. Activities & Tasks
        CrmActivity::create([
            'organization_id' => $autoOrg->id,
            'contact_id' => $contact1->id,
            'deal_id' => $deal1->id,
            'user_id' => $autoAgent->id,
            'type' => CrmActivity::TYPE_CALL,
            'subject' => 'Follow-up Call on Pricing and Delivery',
            'description' => 'Discussed bank wire vs SantimPay payment. Customer requested test drive tomorrow at 10:00 AM.',
            'occurred_at' => now()->subHours(5),
        ]);

        Task::create([
            'organization_id' => $autoOrg->id,
            'assigned_user_id' => $autoAgent->id,
            'contact_id' => $contact1->id,
            'lead_id' => $lead1->id,
            'title' => 'Prepare Test Drive Vehicle & Inspection Sheet',
            'description' => 'Clean Land Cruiser and prepare temporary trade registration plates.',
            'priority' => 'urgent',
            'status' => 'pending',
            'due_date' => now()->addHours(14),
        ]);

        Task::create([
            'organization_id' => $autoOrg->id,
            'assigned_user_id' => $autoAgent->id,
            'contact_id' => $contact2->id,
            'lead_id' => $lead2->id,
            'title' => 'Send Hybrid Warranty & Spec Sheet',
            'priority' => 'medium',
            'status' => 'pending',
            'due_date' => now()->addHours(20),
        ]);

        Appointment::create([
            'organization_id' => $autoOrg->id,
            'contact_id' => $contact1->id,
            'listing_id' => $car1->id,
            'assigned_user_id' => $autoAgent->id,
            'title' => 'Test Drive - Land Cruiser VXR',
            'type' => 'test_drive',
            'status' => Appointment::STATUS_CONFIRMED,
            'start_time' => now()->addHours(16),
            'end_time' => now()->addHours(17),
            'location' => 'Bole Medhanialem Showroom',
        ]);

        // 10. Completed Demo Order & Verified Payment
        $demoOrder = Order::create([
            'organization_id' => $autoOrg->id,
            'user_id' => $customer->id,
            'contact_id' => $contact1->id,
            'listing_id' => $car1->id,
            'order_number' => 'ORD-DEMO-2026',
            'purchase_type' => Order::TYPE_VEHICLE_DEPOSIT,
            'total_amount' => 185000, // 1% reservation deposit
            'currency' => 'ETB',
            'status' => Order::STATUS_CONFIRMED,
            'payment_status' => Order::PAYMENT_PAID,
            'customer_name' => 'Hailu Merid',
            'customer_email' => 'hailu.merid@gmail.com',
            'customer_phone' => '+251911445566',
        ]);

        OrderItem::create([
            'order_id' => $demoOrder->id,
            'listing_id' => $car1->id,
            'item_name' => 'Vehicle Reservation Deposit: 2023 Toyota Land Cruiser VXR',
            'quantity' => 1,
            'unit_price' => 185000,
            'total_price' => 185000,
        ]);

        Payment::create([
            'organization_id' => $autoOrg->id,
            'order_id' => $demoOrder->id,
            'provider' => Payment::PROVIDER_SANTIMPAY,
            'transaction_reference' => 'SANTIM-DEMO-998811',
            'provider_reference' => 'SP-REF-00129',
            'amount' => 185000,
            'currency' => 'ETB',
            'status' => Payment::STATUS_VERIFIED,
            'verified_at' => now(),
            'response_payload' => ['message' => 'Verified SantimPay mobile payment transaction'],
        ]);
    }
}
