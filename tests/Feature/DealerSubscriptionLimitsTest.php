<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Organization;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\AI\AIService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DealerSubscriptionLimitsTest extends TestCase
{
    use RefreshDatabase;

    protected SubscriptionPlan $basicPlan;
    protected SubscriptionPlan $premiumPlan;
    protected SubscriptionPlan $advancePlan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->basicPlan = SubscriptionPlan::create([
            'name' => 'Dealer Basic',
            'slug' => 'dealer-basic',
            'price' => 5000.00,
            'currency' => 'ETB',
            'interval' => 'monthly',
            'listing_limit' => 150,
            'contact_limit' => 500,
            'user_limit' => 2,
            'ai_request_limit' => 200,
            'features' => [
                'posts_per_day' => 5,
                'customer_phone_access' => false,
                'ai_customer_assistant' => false,
                'ai_username_branding' => false,
            ],
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->premiumPlan = SubscriptionPlan::create([
            'name' => 'Dealer Premium',
            'slug' => 'dealer-premium',
            'price' => 8000.00,
            'currency' => 'ETB',
            'interval' => 'monthly',
            'listing_limit' => 600,
            'contact_limit' => 2500,
            'user_limit' => 5,
            'ai_request_limit' => 1000,
            'features' => [
                'posts_per_day' => 20,
                'customer_phone_access' => true,
                'ai_customer_assistant' => true,
                'ai_username_branding' => false,
            ],
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $this->advancePlan = SubscriptionPlan::create([
            'name' => 'Dealer Advance',
            'slug' => 'dealer-advance',
            'price' => 12000.00,
            'currency' => 'ETB',
            'interval' => 'monthly',
            'listing_limit' => 99999,
            'contact_limit' => 99999,
            'user_limit' => 99999,
            'ai_request_limit' => 5000,
            'features' => [
                'posts_per_day' => -1,
                'customer_phone_access' => true,
                'ai_customer_assistant' => true,
                'ai_username_branding' => true,
            ],
            'is_active' => true,
            'sort_order' => 3,
        ]);
    }

    public function test_basic_dealer_is_restricted_to_5_listing_posts_per_day()
    {
        $org = Organization::create([
            'name' => 'Boutique Motors',
            'slug' => 'boutique-motors',
            'subdomain' => 'boutique',
            'currency' => 'ETB',
            'subscription_plan_id' => $this->basicPlan->id,
            'status' => 'active',
        ]);

        $dealer = User::create([
            'organization_id' => $org->id,
            'name' => 'Abebe Seller',
            'email' => 'abebe@boutique.com',
            'phone' => '+251911000111',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ORGANIZATION_ADMIN,
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Vehicles',
            'slug' => 'vehicles',
        ]);

        $this->actingAs($dealer);

        // Post 5 listings (allowed on Basic)
        for ($i = 1; $i <= 5; $i++) {
            $res = $this->post(route('dealer.listings.store'), [
                'category_id' => $category->id,
                'title' => "Test Car {$i}",
                'price' => 1500000,
                'currency' => 'ETB',
                'price_type' => 'fixed',
            ]);
            $res->assertSessionHasNoErrors();
        }

        $this->assertEquals(5, $org->postsTodayCount());
        $this->assertFalse($org->canPostItemToday());

        // 6th listing attempt should be blocked
        $blockedRes = $this->post(route('dealer.listings.store'), [
            'category_id' => $category->id,
            'title' => "Blocked Car 6",
            'price' => 1500000,
            'currency' => 'ETB',
            'price_type' => 'fixed',
        ]);

        $blockedRes->assertSessionHas('error');
        $this->assertEquals(5, $org->postsTodayCount()); // Still 5
    }

    public function test_customer_phone_access_is_masked_for_basic_and_revealed_for_premium()
    {
        // Basic dealer
        $basicOrg = Organization::create([
            'name' => 'Basic Electronics',
            'slug' => 'basic-electronics',
            'currency' => 'ETB',
            'subscription_plan_id' => $this->basicPlan->id,
            'status' => 'active',
        ]);
        $this->assertFalse($basicOrg->canAccessCustomerPhone());

        // Premium dealer
        $premiumOrg = Organization::create([
            'name' => 'Premium Properties',
            'slug' => 'premium-properties',
            'currency' => 'ETB',
            'subscription_plan_id' => $this->premiumPlan->id,
            'status' => 'active',
        ]);
        $this->assertTrue($premiumOrg->canAccessCustomerPhone());

        // Advance dealer
        $advanceOrg = Organization::create([
            'name' => 'Advance Auto Group',
            'slug' => 'advance-auto',
            'currency' => 'ETB',
            'subscription_plan_id' => $this->advancePlan->id,
            'status' => 'active',
        ]);
        $this->assertTrue($advanceOrg->canAccessCustomerPhone());
    }

    public function test_advance_tier_activates_username_branded_ai_assistant()
    {
        $advanceOrg = Organization::create([
            'name' => 'Zacma Luxury Motors',
            'slug' => 'zacma-luxury-motors',
            'subdomain' => 'zacmaluxury',
            'currency' => 'ETB',
            'subscription_plan_id' => $this->advancePlan->id,
            'status' => 'active',
        ]);

        $advanceDealer = User::create([
            'organization_id' => $advanceOrg->id,
            'name' => 'Tadesse Director',
            'email' => 'tadesse@luxury.com',
            'phone' => '+251911444333',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ORGANIZATION_ADMIN,
            'is_active' => true,
        ]);

        $this->assertTrue($advanceOrg->hasUsernameBrandedAi());
        $this->assertEquals('@zacmaluxury AI Customer Assistant', $advanceOrg->aiAssistantBrandedName());

        $aiService = app(AIService::class);
        $chat = $aiService->chat('Tell me about your dealership and inventory', $advanceDealer);

        $this->assertNotEmpty($chat['reply']);
    }

    public function test_dealer_can_upgrade_subscription_package()
    {
        $org = Organization::create([
            'name' => 'Small Brokerage',
            'slug' => 'small-brokerage',
            'currency' => 'ETB',
            'subscription_plan_id' => $this->basicPlan->id,
            'status' => 'active',
        ]);

        $dealer = User::create([
            'organization_id' => $org->id,
            'name' => 'Yared Broker',
            'email' => 'yared@broker.com',
            'phone' => '+251911999111',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ORGANIZATION_ADMIN,
            'is_active' => true,
        ]);

        $this->actingAs($dealer);

        // Can access subscription page without error
        $viewRes = $this->get(route('dealer.subscription.index'));
        $viewRes->assertOk();
        $viewRes->assertSee('Dealer Basic');
        $viewRes->assertSee('Dealer Premium');
        $viewRes->assertSee('Dealer Advance');

        // Test checkout screen loads with available payment providers
        $checkoutRes = $this->get(route('checkout.subscription.show', $this->advancePlan->id));
        $checkoutRes->assertOk();
        $checkoutRes->assertSee('Telebirr');
        $checkoutRes->assertSee('PayPal');
        $checkoutRes->assertSee('SantimPay');
        $checkoutRes->assertSee('Chapa');

        // Process subscription payment with Telebirr / Card / Cash
        $payRes = $this->post(route('checkout.subscription.process', $this->advancePlan->id), [
            'provider' => 'cash',
            'business_name' => 'Small Brokerage',
            'contact_name' => 'Yared Broker',
            'contact_email' => 'yared@broker.com',
            'contact_phone' => '+251911999111',
        ]);

        $payRes->assertRedirect();
        $org->refresh();

        $this->assertEquals($this->advancePlan->id, $org->subscription_plan_id);
        $this->assertTrue($org->hasUsernameBrandedAi());
        $this->assertEquals(-1, $org->dailyPostsAllowed()); // Unlimited
    }

    public function test_super_admin_without_organization_can_view_subscription_page_safely()
    {
        $superAdmin = User::create([
            'organization_id' => null,
            'name' => 'System Root',
            'email' => 'root@zacma.com',
            'phone' => '+251911999000',
            'password' => bcrypt('password'),
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->actingAs($superAdmin);

        $response = $this->get(route('dealer.subscription.index'));
        $response->assertOk();
        $response->assertSee('Dealer Basic');
        $response->assertSee('Dealer Premium');
        $response->assertSee('Dealer Advance');
    }
}
