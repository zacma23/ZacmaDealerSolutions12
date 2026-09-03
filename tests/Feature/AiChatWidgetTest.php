<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiChatWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_receives_role_tailored_ai_advice()
    {
        $superAdmin = User::create([
            'name' => 'Root Admin',
            'email' => 'root@zacma.com',
            'phone' => '+251911999888',
            'password' => bcrypt('password'),
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $response = $this->actingAs($superAdmin)->postJson(route('ai.chat'), [
            'message' => 'Summarize platform metrics and system health',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'user_role' => User::ROLE_SUPER_ADMIN,
        ]);
        $this->assertNotEmpty($response->json('reply'));
    }

    public function test_dealer_receives_crm_copilot_advice()
    {
        $org = Organization::create([
            'name' => 'Addis Prime Cars',
            'slug' => 'addis-prime-cars',
            'subdomain' => 'addisprime',
            'currency' => 'ETB',
            'status' => 'active',
        ]);

        $dealer = User::create([
            'organization_id' => $org->id,
            'name' => 'Dealer Abebe',
            'email' => 'abebe@addisprime.com',
            'phone' => '+251911777666',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ORGANIZATION_ADMIN,
            'is_active' => true,
        ]);

        $response = $this->actingAs($dealer)->postJson(route('ai.chat'), [
            'message' => 'Who are my hot leads and pending tasks today?',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'user_role' => User::ROLE_ORGANIZATION_ADMIN,
        ]);
        $this->assertNotEmpty($response->json('reply'));
    }

    public function test_customer_receives_shopping_concierge_advice()
    {
        $customer = User::create([
            'name' => 'Buyer Sara',
            'email' => 'sara@buyer.com',
            'phone' => '+251911555444',
            'password' => bcrypt('password'),
            'role' => User::ROLE_CUSTOMER,
            'is_active' => true,
        ]);

        $response = $this->actingAs($customer)->postJson(route('ai.chat'), [
            'message' => 'How can I book a test drive appointment for a vehicle?',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'user_role' => User::ROLE_CUSTOMER,
        ]);
        $this->assertNotEmpty($response->json('reply'));
    }

    public function test_guest_receives_marketplace_guidance()
    {
        $response = $this->postJson(route('ai.chat'), [
            'message' => 'What categories can I browse on this platform?',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'user_role' => 'GUEST',
        ]);
        $this->assertNotEmpty($response->json('reply'));

        // Check history retrieval and clearing
        $historyRes = $this->getJson(route('ai.chat.history'));
        $historyRes->assertOk();
        $this->assertCount(2, $historyRes->json('history'));

        $clearRes = $this->postJson(route('ai.chat.clear'));
        $clearRes->assertOk();

        $historyRes2 = $this->getJson(route('ai.chat.history'));
        $this->assertCount(0, $historyRes2->json('history'));
    }
}
