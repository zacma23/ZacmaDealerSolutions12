<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Listing;
use App\Models\Organization;
use App\Models\Scopes\TenantScope;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $orgA;
    protected Organization $orgB;
    protected User $userA;
    protected User $userB;
    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orgA = Organization::create([
            'name' => 'Organization Alpha',
            'slug' => 'org-alpha',
            'subdomain' => 'alpha',
            'currency' => 'ETB',
            'status' => 'active',
        ]);

        $this->orgB = Organization::create([
            'name' => 'Organization Beta',
            'slug' => 'org-beta',
            'subdomain' => 'beta',
            'currency' => 'ETB',
            'status' => 'active',
        ]);

        $this->userA = User::create([
            'organization_id' => $this->orgA->id,
            'name' => 'Agent Alpha',
            'email' => 'agent.alpha@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ORGANIZATION_ADMIN,
            'is_active' => true,
        ]);

        $this->userB = User::create([
            'organization_id' => $this->orgB->id,
            'name' => 'Agent Beta',
            'email' => 'agent.beta@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ORGANIZATION_ADMIN,
            'is_active' => true,
        ]);

        $this->superAdmin = User::create([
            'name' => 'Global Admin',
            'email' => 'global.admin@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);
    }

    public function test_organization_a_cannot_see_organization_b_crm_contacts()
    {
        // Create contact for Org B
        $contactB = Contact::create([
            'organization_id' => $this->orgB->id,
            'first_name' => 'Confidential',
            'last_name' => 'Client Beta',
            'email' => 'beta.client@test.com',
            'contact_type' => Contact::TYPE_CUSTOMER,
            'status' => Contact::STATUS_CUSTOMER,
        ]);

        // When User A is authenticated
        $this->actingAs($this->userA);

        // Model query with TenantScope must return empty
        $contactsForA = Contact::all();
        $this->assertFalse($contactsForA->contains($contactB));
        $this->assertEquals(0, Contact::where('id', $contactB->id)->count());

        // Visiting Customer 360 directly of Org B must fail / 404
        $response = $this->get(route('dealer.crm.contacts.360', $contactB->id));
        $response->assertNotFound();
    }

    public function test_organization_a_cannot_see_organization_b_leads()
    {
        $contactB = Contact::create([
            'organization_id' => $this->orgB->id,
            'first_name' => 'LeadContact',
            'last_name' => 'Beta',
            'email' => 'lead.beta@test.com',
            'contact_type' => Contact::TYPE_LEAD,
            'status' => Contact::STATUS_LEAD,
        ]);

        $leadB = Lead::create([
            'organization_id' => $this->orgB->id,
            'contact_id' => $contactB->id,
            'title' => 'High Value Deal Beta',
            'status' => Lead::STATUS_NEW,
            'priority' => Lead::PRIORITY_URGENT,
            'estimated_value' => 5000000,
            'currency' => 'ETB',
        ]);

        $this->actingAs($this->userA);

        $leadsForA = Lead::all();
        $this->assertFalse($leadsForA->contains($leadB));
    }

    public function test_super_admin_can_access_cross_tenant_data()
    {
        $contactB = Contact::create([
            'organization_id' => $this->orgB->id,
            'first_name' => 'Beta',
            'last_name' => 'Buyer',
            'email' => 'buyer@beta.com',
            'contact_type' => Contact::TYPE_CUSTOMER,
            'status' => Contact::STATUS_CUSTOMER,
        ]);

        $this->actingAs($this->superAdmin);

        $response = $this->get(route('super-admin.crm.index'));
        $response->assertOk();
    }

    public function test_unauthenticated_users_are_redirected_to_login()
    {
        $response = $this->get(route('dealer.dashboard'));
        $response->assertRedirect(route('login'));

        $responseAdmin = $this->get(route('super-admin.dashboard'));
        $responseAdmin->assertRedirect(route('login'));
    }

    public function test_dealer_cannot_access_super_admin_portal()
    {
        $this->actingAs($this->userA);

        $response = $this->get(route('super-admin.dashboard'));
        $response->assertForbidden();
    }
}
