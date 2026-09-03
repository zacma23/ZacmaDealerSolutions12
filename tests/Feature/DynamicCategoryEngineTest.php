<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingFieldValue;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DynamicCategoryEngineTest extends TestCase
{
    use RefreshDatabase;
    public function test_dynamic_categories_and_custom_fields_can_be_created()
    {
        // 1. Create a custom category for "Solar & Green Energy" without modifying code
        $category = Category::create([
            'name' => 'Solar & Green Energy',
            'slug' => 'solar-energy',
            'icon' => 'fa-solid fa-solar-panel',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('categories', ['slug' => 'solar-energy']);

        // 2. Add dynamic custom attributes
        $capacityField = $category->fields()->create([
            'name' => 'power_output_kw',
            'label' => 'Power Output',
            'field_type' => 'number',
            'unit' => 'kW',
            'is_filterable' => true,
        ]);

        $batteryField = $category->fields()->create([
            'name' => 'battery_included',
            'label' => 'Battery Storage Included',
            'field_type' => 'boolean',
            'is_filterable' => true,
        ]);

        $this->assertCount(2, $category->fields);

        // 3. Create organization & listing with these dynamic attributes
        $org = Organization::create([
            'name' => 'Green Energy Ethiopia',
            'slug' => 'green-energy',
            'subdomain' => 'green',
            'currency' => 'ETB',
            'status' => 'active',
        ]);

        $user = User::create([
            'organization_id' => $org->id,
            'name' => 'Solar Engineer',
            'email' => 'solar@greenenergy.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ORGANIZATION_ADMIN,
        ]);

        $listing = Listing::create([
            'organization_id' => $org->id,
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Commercial 50kW Hybrid Solar Inverter System',
            'slug' => '50kw-hybrid-solar-system',
            'price' => 1200000,
            'currency' => 'ETB',
            'price_type' => 'fixed',
            'status' => Listing::STATUS_PUBLISHED,
        ]);

        ListingFieldValue::create([
            'listing_id' => $listing->id,
            'category_field_id' => $capacityField->id,
            'value' => '50',
            'numeric_value' => 50,
        ]);

        ListingFieldValue::create([
            'listing_id' => $listing->id,
            'category_field_id' => $batteryField->id,
            'value' => 'Yes',
        ]);

        // 4. Verify listing dynamic values
        $this->assertCount(2, $listing->fieldValues);
        $this->assertEquals(50, $listing->getFieldValue('power_output_kw'));
        $this->assertEquals('Yes', $listing->getFieldValue('battery_included'));

        // 5. Test browse filtering on public marketplace
        $response = $this->get(route('marketplace.browse', [
            'q' => 'Hybrid Solar',
            'fields' => ['power_output_kw' => '50'],
        ]));

        $response->assertOk();
        $response->assertSee('Commercial 50kW Hybrid Solar Inverter System');
    }
}
