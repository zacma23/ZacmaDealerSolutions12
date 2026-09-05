<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\AI\AIService;
use App\Services\AI\SystemKnowledgeBase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiAssistantSystemKnowledgeTest extends TestCase
{
    use RefreshDatabase;

    protected User $dealer;
    protected Organization $org;

    protected function setUp(): void
    {
        parent::setUp();

        $plan = SubscriptionPlan::create([
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

        $this->org = Organization::create([
            'name' => 'Addis Prime Dealership',
            'slug' => 'addis-prime',
            'subdomain' => 'addisprime',
            'currency' => 'ETB',
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
        ]);

        $this->dealer = User::create([
            'organization_id' => $this->org->id,
            'name' => 'Dawit Manager',
            'email' => 'dawit@addisprime.com',
            'phone' => '+251911334455',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ORGANIZATION_ADMIN,
            'is_active' => true,
        ]);
    }

    public function test_system_knowledge_base_generates_comprehensive_prompt()
    {
        $prompt = SystemKnowledgeBase::buildFullSystemPrompt($this->dealer);

        $this->assertStringContainsString('Zacma AI Platform', $prompt);
        $this->assertStringContainsString('Dealer Basic (5,000 ETB / month)', $prompt);
        $this->assertStringContainsString('Dealer Premium (8,000 ETB / month)', $prompt);
        $this->assertStringContainsString('Dealer Advance (12,000 ETB / month)', $prompt);
        $this->assertStringContainsString('Telebirr', $prompt);
        $this->assertStringContainsString('SantimPay', $prompt);
        $this->assertStringContainsString('Chapa', $prompt);
        $this->assertStringContainsString('PayPal', $prompt);
        $this->assertStringContainsString('Crypto', $prompt);
        $this->assertStringContainsString('Gemini Multimodal Vision', $prompt);
        $this->assertStringContainsString('@addisprime AI Customer Assistant', $prompt);
    }

    public function test_ai_assistant_answers_subscription_knowledge_questions()
    {
        $aiService = app(AIService::class);
        $chat = $aiService->chat('What are the subscription plans and pricing for dealers?', $this->dealer);

        $this->assertTrue($chat['success']);
        $this->assertStringContainsString('5,000', $chat['reply']);
        $this->assertStringContainsString('8,000', $chat['reply']);
        $this->assertStringContainsString('12,000', $chat['reply']);
        $this->assertStringContainsString('Dealer Basic', $chat['reply']);
        $this->assertStringContainsString('Dealer Advance', $chat['reply']);
    }

    public function test_ai_assistant_answers_payment_gateway_knowledge_questions()
    {
        $aiService = app(AIService::class);
        $chat = $aiService->chat('What payment gateways are integrated in the system?', $this->dealer);

        $this->assertTrue($chat['success']);
        $this->assertStringContainsString('Telebirr', $chat['reply']);
        $this->assertStringContainsString('SantimPay', $chat['reply']);
        $this->assertStringContainsString('Chapa', $chat['reply']);
        $this->assertStringContainsString('PayPal', $chat['reply']);
        $this->assertStringContainsString('Crypto', $chat['reply']);
    }

    public function test_ai_assistant_answers_system_overview_questions()
    {
        $aiService = app(AIService::class);
        $chat = $aiService->chat('What is the Zacma platform and what categories does it support?', $this->dealer);

        $this->assertTrue($chat['success']);
        $this->assertStringContainsString('Zacma AI Platform', $chat['reply']);
        $this->assertStringContainsString('Vehicles', $chat['reply']);
        $this->assertStringContainsString('Real Estate', $chat['reply']);
        $this->assertStringContainsString('Electronics', $chat['reply']);
    }

    public function test_ai_chat_endpoint_responds_to_system_questions_via_api()
    {
        $this->actingAs($this->dealer);

        $response = $this->postJson(route('ai.chat'), [
            'message' => 'Tell me about the subscription packages and how to upgrade',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['success', 'reply']);
        $this->assertStringContainsString('Subscription', $response->json('reply'));
    }
}
