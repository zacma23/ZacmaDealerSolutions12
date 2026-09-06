<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Listing;
use App\Models\Organization;
use App\Models\Order;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminCentralCommandTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $dealerUser;
    protected User $customerUser;
    protected Organization $dealerOrg;
    protected Category $vehicleCategory;
    protected Listing $vehicleListing;

    protected function setUp(): void
    {
        parent::setUp();

        $plan = SubscriptionPlan::create([
            'name' => 'Enterprise Pro',
            'slug' => 'enterprise-pro',
            'price' => 5000,
            'currency' => 'ETB',
            'interval' => 'monthly',
            'listing_limit' => 100,
            'contact_limit' => 500,
            'user_limit' => 20,
            'ai_request_limit' => 200,
            'is_active' => true,
        ]);

        $this->dealerOrg = Organization::create([
            'name' => 'Addis Luxury Motors',
            'slug' => 'addis-luxury-motors',
            'currency' => 'ETB',
            'status' => 'active',
            'subscription_plan_id' => $plan->id,
        ]);

        $this->superAdmin = User::create([
            'name' => 'Global Super Administrator',
            'email' => 'admin@zacma.com',
            'password' => Hash::make('Secret123!'),
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->dealerUser = User::create([
            'organization_id' => $this->dealerOrg->id,
            'name' => 'Dealer Manager Dawit',
            'email' => 'dawit@addismotors.com',
            'password' => Hash::make('Secret123!'),
            'role' => User::ROLE_ORGANIZATION_ADMIN,
            'is_active' => true,
        ]);

        $this->customerUser = User::create([
            'name' => 'Customer Meron',
            'email' => 'meron@gmail.com',
            'password' => Hash::make('Secret123!'),
            'role' => User::ROLE_CUSTOMER,
            'is_active' => true,
        ]);

        $this->vehicleCategory = Category::create([
            'name' => 'Vehicles',
            'slug' => 'vehicles',
            'icon' => 'fa-car',
            'is_active' => true,
        ]);

        $this->vehicleListing = Listing::create([
            'organization_id' => $this->dealerOrg->id,
            'user_id' => $this->dealerUser->id,
            'category_id' => $this->vehicleCategory->id,
            'title' => '2024 Toyota Land Cruiser 300 VXR',
            'slug' => '2024-toyota-land-cruiser-300-vxr',
            'description' => 'Brand new Toyota Land Cruiser 300 with executive package.',
            'price' => 25000000,
            'currency' => 'ETB',
            'status' => Listing::STATUS_PUBLISHED,
            'approval_status' => 'pending',
            'featured' => false,
        ]);
    }

    public function test_super_admin_can_access_all_command_center_modules()
    {
        $this->actingAs($this->superAdmin);

        $routes = [
            route('super-admin.dashboard'),
            route('super-admin.auto.index'),
            route('super-admin.property.index'),
            route('super-admin.electronics.index'),
            route('super-admin.listings.index'),
            route('super-admin.users.index'),
            route('super-admin.customers.index'),
            route('super-admin.sales-agents.index'),
            route('super-admin.orders.index'),
            route('super-admin.payments.index'),
            route('super-admin.search', ['q' => 'Toyota']),
        ];

        foreach ($routes as $url) {
            $response = $this->get($url);
            $response->assertOk();
        }
    }

    public function test_non_super_admin_cannot_access_super_admin_routes()
    {
        $this->actingAs($this->dealerUser);

        $response = $this->get(route('super-admin.dashboard'));
        $this->assertContains($response->status(), [403, 302]);
    }

    public function test_super_admin_can_moderate_listings()
    {
        $this->actingAs($this->superAdmin);

        // 1. Approve
        $resp = $this->post(route('super-admin.listings.approve', $this->vehicleListing->id));
        $resp->assertSessionHas('success');
        $this->assertEquals('approved', $this->vehicleListing->fresh()->approval_status);

        // 2. Reject
        $resp = $this->post(route('super-admin.listings.reject', $this->vehicleListing->id));
        $resp->assertSessionHas('success');
        $this->assertEquals('rejected', $this->vehicleListing->fresh()->approval_status);

        // 3. Toggle feature
        $resp = $this->post(route('super-admin.listings.toggle-feature', $this->vehicleListing->id));
        $resp->assertSessionHas('success');
        $this->assertTrue((bool)$this->vehicleListing->fresh()->featured);

        // 4. Delete (soft delete)
        $resp = $this->delete(route('super-admin.listings.destroy', $this->vehicleListing->id));
        $resp->assertSessionHas('success');
        $this->assertSoftDeleted('listings', ['id' => $this->vehicleListing->id]);
    }

    public function test_super_admin_can_impersonate_and_stop_impersonation()
    {
        $this->actingAs($this->superAdmin);

        // Impersonate dealer manager
        $response = $this->post(route('super-admin.impersonate', $this->dealerUser->id));
        $response->assertRedirect($this->dealerUser->getDashboardUrl());
        $this->assertEquals($this->dealerUser->id, auth()->id());
        $this->assertEquals($this->superAdmin->id, session('impersonator_id'));

        // Stop impersonation
        $stopResponse = $this->post(route('super-admin.stop-impersonation'));
        $stopResponse->assertRedirect(route('super-admin.dashboard'));
        $this->assertEquals($this->superAdmin->id, auth()->id());
        $this->assertFalse(session()->has('impersonator_id'));
    }

    public function test_super_admin_can_toggle_user_status()
    {
        $this->actingAs($this->superAdmin);

        $response = $this->post(route('super-admin.users.toggle', $this->dealerUser->id));
        $response->assertSessionHas('success');
        $this->assertFalse($this->dealerUser->fresh()->is_active);

        $response = $this->post(route('super-admin.users.toggle', $this->dealerUser->id));
        $response->assertSessionHas('success');
        $this->assertTrue($this->dealerUser->fresh()->is_active);
    }
}