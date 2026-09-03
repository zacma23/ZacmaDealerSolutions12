<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Listing;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\Scopes\TenantScope;
use App\Models\SubscriptionPlan;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function show(Request $request, Listing $listing)
    {
        $purchaseType = $request->get('type', Order::TYPE_PRODUCT);
        
        // Deposit rate is 10% for vehicles/properties if reserving
        $amount = $listing->price;
        if ($purchaseType === Order::TYPE_VEHICLE_DEPOSIT || $purchaseType === Order::TYPE_PROPERTY_RESERVE) {
            $amount = round($listing->price * 0.10, 2);
        }

        $providers = $this->paymentService->getAvailableProviders();

        return view('checkout.show', compact('listing', 'purchaseType', 'amount', 'providers'));
    }

    public function process(Request $request, Listing $listing)
    {
        $validated = $request->validate([
            'purchase_type' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'provider' => 'required|string',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email',
            'customer_phone' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $orgId = $listing->organization_id;

        // Find or create Contact
        $nameParts = explode(' ', trim($validated['customer_name']), 2);
        $contact = Contact::withoutGlobalScope(TenantScope::class)->firstOrCreate(
            ['organization_id' => $orgId, 'email' => $validated['customer_email']],
            [
                'first_name' => $nameParts[0],
                'last_name' => $nameParts[1] ?? '',
                'phone' => $validated['customer_phone'],
                'contact_type' => Contact::TYPE_CUSTOMER,
                'status' => Contact::STATUS_PROSPECT,
                'source' => 'marketplace_checkout',
            ]
        );

        // Create Order
        $order = Order::create([
            'organization_id' => $orgId,
            'user_id' => Auth::id(),
            'contact_id' => $contact->id,
            'listing_id' => $listing->id,
            'order_number' => 'ORD-' . strtoupper(Str::random(4)) . '-' . time(),
            'purchase_type' => $validated['purchase_type'],
            'total_amount' => $validated['amount'],
            'currency' => $listing->currency,
            'status' => Order::STATUS_PENDING,
            'payment_status' => Order::PAYMENT_UNPAID,
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'],
            'customer_phone' => $validated['customer_phone'],
            'notes' => $validated['notes'] ?? null,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'listing_id' => $listing->id,
            'item_name' => "{$listing->title} ({$validated['purchase_type']})",
            'quantity' => 1,
            'unit_price' => $validated['amount'],
            'total_price' => $validated['amount'],
        ]);

        // Initiate payment
        $paymentResult = $this->paymentService->createPaymentForOrder($order, $validated['provider']);

        if (!$paymentResult['success']) {
            return back()->with('error', 'Unable to initiate payment with selected provider. Please try another method.');
        }

        if (!empty($paymentResult['redirect_url'])) {
            return redirect()->away($paymentResult['redirect_url']);
        }

        return redirect()->route('checkout.success', ['reference' => $paymentResult['payment']->transaction_reference]);
    }

    public function verify(Request $request, string $provider)
    {
        $reference = $request->get('ref') ?? $request->get('transaction_id') ?? $request->get('tx_ref');

        if (!$reference) {
            return redirect()->route('home')->with('error', 'Missing transaction reference.');
        }

        $verified = $this->paymentService->verifyPayment($provider, $reference);

        if ($verified) {
            return redirect()->route('checkout.success', ['reference' => $reference])
                ->with('success', 'Payment verified successfully!');
        }

        return redirect()->route('checkout.cancel', ['ref' => $reference])
            ->with('error', 'Payment verification was not successful.');
    }

    // ==========================================
    // SUBSCRIPTION CHECKOUT
    // ==========================================

    public function showSubscriptionCheckout(Request $request, SubscriptionPlan $plan)
    {
        $providers = $this->paymentService->getAvailableProviders();
        $user = Auth::user();
        $org = $user?->organization;

        return view('checkout.subscription', compact('plan', 'providers', 'user', 'org'));
    }

    public function processSubscriptionCheckout(Request $request, SubscriptionPlan $plan)
    {
        $validated = $request->validate([
            'provider' => 'required|string',
            'business_name' => 'nullable|string|max:255',
            'contact_name' => 'required|string|max:255',
            'contact_email' => 'required|email',
            'contact_phone' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();

        // Ensure user has an organization
        $org = $user?->organization;
        if (!$org) {
            $orgName = $validated['business_name'] ?: ($user ? $user->name . ' Dealership' : 'Zacma Partner');
            $orgSlug = Str::slug($orgName) . '-' . rand(100, 999);

            $org = Organization::create([
                'name' => $orgName,
                'slug' => $orgSlug,
                'subdomain' => Str::slug($orgName),
                'email' => $validated['contact_email'],
                'phone' => $validated['contact_phone'],
                'currency' => $plan->currency,
                'subscription_plan_id' => $plan->id,
                'status' => 'active',
                'subscription_ends_at' => now()->addMonth(),
            ]);

            if ($user) {
                $user->update(['organization_id' => $org->id]);
            }
        }

        // Create Order for Subscription
        $order = Order::create([
            'organization_id' => $org->id,
            'user_id' => $user?->id,
            'order_number' => 'SUB-' . strtoupper(Str::random(4)) . '-' . time(),
            'purchase_type' => Order::TYPE_SUBSCRIPTION,
            'total_amount' => $plan->price,
            'currency' => $plan->currency,
            'status' => Order::STATUS_PENDING,
            'payment_status' => Order::PAYMENT_UNPAID,
            'customer_name' => $validated['contact_name'],
            'customer_email' => $validated['contact_email'],
            'customer_phone' => $validated['contact_phone'],
            'notes' => "Subscription upgrade to {$plan->name}",
            'metadata' => ['plan_id' => $plan->id],
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'item_name' => "Subscription Plan: {$plan->name} (1 Month)",
            'quantity' => 1,
            'unit_price' => $plan->price,
            'total_price' => $plan->price,
        ]);

        // Initiate payment
        $paymentResult = $this->paymentService->createPaymentForOrder($order, $validated['provider']);

        if (!$paymentResult['success']) {
            return back()->with('error', 'Unable to initiate payment with selected provider. Please try another method.');
        }

        // If provider is instant (cash, sandbox, crypto simulated), finalize immediately
        if (in_array($validated['provider'], [Payment::PROVIDER_CASH, Payment::PROVIDER_CRYPTO])) {
            $this->paymentService->finalizeSuccessfulPayment($paymentResult['payment']);
        }

        if (!empty($paymentResult['redirect_url'])) {
            return redirect()->away($paymentResult['redirect_url']);
        }

        return redirect()->route('checkout.success', ['reference' => $paymentResult['payment']->transaction_reference]);
    }

    public function success(Request $request)
    {
        $reference = $request->get('reference');
        $payment = Payment::with(['order.items', 'order.listing', 'order.invoice', 'order.organization'])
            ->where('transaction_reference', $reference)
            ->first();

        // Finalize test/sandbox payments automatically so user gets their upgrade immediately
        if ($payment && !$payment->isVerified()) {
            $this->paymentService->finalizeSuccessfulPayment($payment);
        }

        return view('checkout.success', compact('payment', 'reference'));
    }

    public function cancel(Request $request)
    {
        $reference = $request->get('ref');
        return view('checkout.cancel', compact('reference'));
    }
}
