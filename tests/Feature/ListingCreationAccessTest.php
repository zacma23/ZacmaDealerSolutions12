<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingCreationAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_authenticated_roles_can_access_create_listing_form()
    {
        $org = Organization::create([
            'name' => 'Auto Motors',
            'slug' => 'auto-motors',
            'subdomain' => 'automotors',
            'currency' => 'ETB',
            'status' => 'active',
        ]);

        $category = Category::create([
            'name' => 'Vehicles',
            'slug' => 'vehicles',
            'icon' => 'truck',
        ]);

        $roles = [
            'ORGANIZATION_ADMIN',
            'SALES_AGENT',
            'SELLER',
            'CUSTOMER',
            'SUPER_ADMIN',
        ];

        foreach ($roles as $role) {
            $user = User::create([
                'organization_id' => $role === 'SUPER_ADMIN' ? null : $org->id,
                'name' => "User {$role}",
                'email' => strtolower($role) . '@test.com',
                'phone' => '+251911' . rand(100000, 999999),
                'password' => bcrypt('password'),
                'role' => $role,
                'is_active' => true,
            ]);

            $response = $this->actingAs($user)->get(route('dealer.listings.create'));
            $response->assertStatus(200);
        }
    }
}
