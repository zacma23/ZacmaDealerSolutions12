<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Listing;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\User;
use App\Services\Payment\CashSandboxProvider;
use App\Services\Payment\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentVerificationTest extends TestCase
{
    use RefreshDatabase;
    public function test_payment_service_processes_and_verifies_payment_securely()
    {
        $org = Organization::create([
            'name' => 'Apex Dealership',
            'slug' => 'apex-dealership',
            'subdomain' => 'apex',
            'currency' => 'ETB',
            'status' => 'active',
        ]);

        $category = Category::create([
            'name' => 'Vehicles',
            'slug' => 'vehicles-test',
        ]);

        $user = User::create([
            'organization_id' => $org->id,
            'name' => 'Seller Apex',
            'email' => 'apex@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ORGANIZATION_ADMIN,
        ]);

        $listing = Listing::create([
            'organization_id' => $org->id,
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => '2023 Honda CR-V',
            'slug' => '2023-honda-crv-test',
            'price' => 6500000,
            'currency' => 'ETB',
            'price_type' => 'fixed',
            'status' => Listing::STATUS_PUBLISHED,
        ]);

        $contact = Contact::create([
            'organization_id' => $org->id,
            'first_name' => 'Solomon',
            'last_name' => 'Tekle',
            'email' => 'solomon@test.com',
            'phone' => '+251911333444',
            'contact_type' => Contact::TYPE_CUSTOMER,
            'status' => Contact::STATUS_PROSPECT,
        ]);

        $order = Order::create([
            'organization_id' => $org->id,
            'contact_id' => $contact->id,
            'listing_id' => $listing->id,
            'order_number' => 'ORD-TEST-001',
            'purchase_type' => Order::TYPE_VEHICLE_DEPOSIT,
            'total_amount' => 65000,
            'currency' => 'ETB',
            'status' => Order::STATUS_PENDING,
            'payment_status' => Order::PAYMENT_UNPAID,
            'customer_name' => 'Solomon Tekle',
            'customer_email' => 'solomon@test.com',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'listing_id' => $listing->id,
            'item_name' => 'Honda CR-V Reservation Deposit',
            'quantity' => 1,
            'unit_price' => 65000,
            'total_price' => 65000,
        ]);

        // 1. Authenticate user & initialize payment via PaymentService
        $this->actingAs($user);
        $service = app(PaymentService::class);
        $result = $service->createPaymentForOrder($order, Payment::PROVIDER_CASH);

        $this->assertTrue($result['success']);
        $payment = $result['payment'];
        $this->assertEquals(Payment::STATUS_PENDING, $payment->status);

        // 2. Finalize / Verify payment
        $service->finalizeSuccessfulPayment($payment);

        $payment->refresh();
        $order->refresh();

        // 3. Assert verified states
        $this->assertEquals(Payment::STATUS_VERIFIED, $payment->status);
        $this->assertNotNull($payment->verified_at);
        $this->assertEquals(Order::STATUS_CONFIRMED, $order->status);
        $this->assertEquals(Order::PAYMENT_PAID, $order->payment_status);

        // 4. Assert invoice was generated
        $this->assertNotNull($order->invoice);
        $this->assertEquals('paid', $order->invoice->status);

        // 5. Assert CRM activity was logged on customer timeline
        $this->assertDatabaseHas('crm_activities', [
            'organization_id' => $org->id,
            'contact_id' => $contact->id,
            'type' => 'payment',
        ]);
    }
}
