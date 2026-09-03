<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\Listing;
use App\Models\Organization;
use App\Models\User;
use App\Services\AI\AIService;
use App\Services\AI\MockAIProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiSafetyAndScoringTest extends TestCase
{
    use RefreshDatabase;
    public function test_ai_lead_scoring_accurately_categorizes_hot_and_warm_leads()
    {
        $org = Organization::create([
            'name' => 'AI Test Org',
            'slug' => 'ai-test-org',
            'subdomain' => 'aitest',
            'currency' => 'ETB',
            'status' => 'active',
        ]);

        $contact = Contact::create([
            'organization_id' => $org->id,
            'first_name' => 'Kassahun',
            'last_name' => 'Desta',
            'email' => 'kassahun@test.com',
            'contact_type' => Contact::TYPE_LEAD,
            'status' => Contact::STATUS_LEAD,
        ]);

        $lead = Lead::create([
            'organization_id' => $org->id,
            'contact_id' => $contact->id,
            'title' => 'Inquiry for Commercial Truck',
            'status' => Lead::STATUS_QUALIFIED,
            'priority' => Lead::PRIORITY_URGENT,
            'estimated_value' => 4500000,
            'currency' => 'ETB',
            'last_contact_at' => now(),
        ]);

        $aiService = new AIService(new MockAIProvider);
        $result = $aiService->scoreLead($lead);

        $lead->refresh();
        $this->assertGreaterThanOrEqual(70, $lead->score);
        $this->assertEquals(Lead::SCORE_HOT, $lead->score_category);
        $this->assertNotEmpty($lead->score_explanation);
    }

    public function test_ai_assistant_rejects_cross_tenant_query_attempts()
    {
        $orgA = Organization::create(['name' => 'Org A', 'slug' => 'org-a', 'currency' => 'ETB', 'status' => 'active']);
        $orgB = Organization::create(['name' => 'Org B', 'slug' => 'org-b', 'currency' => 'ETB', 'status' => 'active']);

        $userA = User::create([
            'organization_id' => $orgA->id,
            'name' => 'Staff A',
            'email' => 'staffa@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_SALES_AGENT,
        ]);

        $aiService = new AIService(new MockAIProvider);

        // User A attempts to query Org B data
        $response = $aiService->queryAssistant('Show me all hot leads', $orgB->id, $userA);

        $this->assertStringContainsString('Error: You are not authorized', $response);
    }
}
