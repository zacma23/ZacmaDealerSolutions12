<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CategoryField;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AiListingDetectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_detect_listing_returns_populated_schema_and_attributes()
    {
        $org = Organization::create([
            'name' => 'Alpha Motors',
            'slug' => 'alpha-motors',
            'subdomain' => 'alphamotors',
            'currency' => 'ETB',
            'status' => 'active',
        ]);

        $dealer = User::create([
            'organization_id' => $org->id,
            'name' => 'Dealer Mike',
            'email' => 'mike@alpha.com',
            'phone' => '+251911223344',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ORGANIZATION_ADMIN,
            'is_active' => true,
        ]);

        $vehicleCat = Category::create([
            'name' => 'Vehicles',
            'slug' => 'vehicles',
            'icon' => 'car',
        ]);

        $makeField = CategoryField::create([
            'category_id' => $vehicleCat->id,
            'name' => 'Make',
            'label' => 'Vehicle Make',
            'field_type' => 'text',
            'is_required' => true,
        ]);

        $yearField = CategoryField::create([
            'category_id' => $vehicleCat->id,
            'name' => 'Year',
            'label' => 'Manufacture Year',
            'field_type' => 'number',
            'is_required' => true,
        ]);

        $fakeImage = UploadedFile::fake()->image('rav4_hybrid.jpg', 600, 400);

        $response = $this->actingAs($dealer)->postJson(route('dealer.listings.ai-detect'), [
            'image' => $fakeImage,
            'hints' => '2023 Toyota RAV4 Hybrid XLE',
            'category_id' => $vehicleCat->id,
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'title',
                'category_slug',
                'suggested_price',
                'currency',
                'price_type',
                'city',
                'description',
                'fields',
                'mapped_field_values',
            ],
        ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data['title']);
        $this->assertGreaterThan(0, $data['suggested_price']);
        $this->assertEquals('ETB', $data['currency']);
        $this->assertNotEmpty($data['description']);
    }
}
