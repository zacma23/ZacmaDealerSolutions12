<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Category;
use App\Models\Contact;
use App\Models\CrmActivity;
use App\Models\Lead;
use App\Models\Listing;
use App\Models\Organization;
use App\Models\Scopes\TenantScope;
use App\Services\AI\AIService;
use App\Services\Automation\AutomationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketplaceController extends Controller
{
    public function index(Request $request)
    {
        $tenant = app()->has('current_organization') ? app('current_organization') : null;

        // Categories available: global + tenant-specific
        $categoriesQuery = Category::where('is_active', true)->whereNull('parent_id')->orderBy('sort_order');
        if ($tenant) {
            $categoriesQuery->where(function ($q) use ($tenant) {
                $q->whereNull('organization_id')->orWhere('organization_id', $tenant->id);
            });
        }
        $categories = $categoriesQuery->get();

        // Featured listings
        $featuredListings = Listing::withoutGlobalScope(TenantScope::class)
            ->with(['category', 'primaryMedia', 'organization'])
            ->where('status', Listing::STATUS_PUBLISHED)
            ->where('featured', true);

        if ($tenant) {
            $featuredListings->where('organization_id', $tenant->id);
        }

        $featured = $featuredListings->take(6)->get();

        // Latest listings
        $latestListings = Listing::withoutGlobalScope(TenantScope::class)
            ->with(['category', 'primaryMedia', 'organization'])
            ->where('status', Listing::STATUS_PUBLISHED)
            ->latest();

        if ($tenant) {
            $latestListings->where('organization_id', $tenant->id);
        }

        $latest = $latestListings->take(8)->get();

        return view('marketplace.index', compact('categories', 'featured', 'latest', 'tenant'));
    }

    public function browse(Request $request, ?string $categorySlug = null)
    {
        $tenant = app()->has('current_organization') ? app('current_organization') : null;

        $category = null;
        if ($categorySlug) {
            $category = Category::where('slug', $categorySlug)->with('fields.options')->firstOrFail();
        }

        $categories = Category::where('is_active', true)->whereNull('parent_id')->get();

        $query = Listing::withoutGlobalScope(TenantScope::class)
            ->with(['category', 'primaryMedia', 'organization', 'fieldValues.categoryField'])
            ->where('status', Listing::STATUS_PUBLISHED);

        if ($tenant) {
            $query->where('organization_id', $tenant->id);
        }

        if ($category) {
            $query->where('category_id', $category->id);
        }

        // Search term
        if ($request->filled('q')) {
            $term = $request->q;
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%")
                  ->orWhere('city', 'like', "%{$term}%")
                  ->orWhere('address', 'like', "%{$term}%");
            });
        }

        // Location filter
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        // Price filters
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->max_price);
        }

        // Dynamic Field Filters (e.g. fields[fuel_type]=Diesel, fields[year_min]=2020)
        if ($request->filled('fields') && is_array($request->fields) && $category) {
            foreach ($request->fields as $fieldKey => $val) {
                if ($val !== '' && $val !== null) {
                    $catField = $category->fields->firstWhere('name', $fieldKey);
                    if ($catField) {
                        $query->whereHas('fieldValues', function ($fv) use ($catField, $val) {
                            $fv->where('category_field_id', $catField->id);
                            if (is_numeric($val)) {
                                $fv->where('numeric_value', $val);
                            } else {
                                $fv->where('value', 'like', "%{$val}%");
                            }
                        });
                    }
                }
            }
        }

        // Sorting
        $sort = $request->get('sort', 'latest');
        match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'popular' => $query->orderBy('views_count', 'desc'),
            default => $query->latest(),
        };

        $listings = $query->paginate(12)->withQueryString();

        return view('marketplace.browse', compact('listings', 'category', 'categories', 'tenant'));
    }

    public function show(string $slug)
    {
        $listing = Listing::withoutGlobalScope(TenantScope::class)
            ->with(['category.fields', 'media', 'user', 'organization', 'fieldValues.categoryField'])
            ->where('slug', $slug)
            ->where('status', Listing::STATUS_PUBLISHED)
            ->firstOrFail();

        $listing->increment('views_count');

        $relatedListings = Listing::withoutGlobalScope(TenantScope::class)
            ->with(['primaryMedia', 'organization'])
            ->where('category_id', $listing->category_id)
            ->where('id', '!=', $listing->id)
            ->where('status', Listing::STATUS_PUBLISHED)
            ->take(4)
            ->get();

        return view('marketplace.show', compact('listing', 'relatedListings'));
    }

    public function submitInquiry(Request $request, Listing $listing)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'message' => 'required|string|max:2000',
            'inquiry_type' => 'nullable|string|in:inquiry,quote,negotiation,test_drive,viewing',
        ]);

        $orgId = $listing->organization_id;

        // 1. Find or create CRM Contact in the dealer's CRM
        $nameParts = explode(' ', trim($validated['name']), 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';

        $contact = Contact::withoutGlobalScope(TenantScope::class)->firstOrCreate(
            ['organization_id' => $orgId, 'email' => $validated['email']],
            [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $validated['phone'] ?? null,
                'contact_type' => Contact::TYPE_LEAD,
                'status' => Contact::STATUS_LEAD,
                'source' => 'listing_inquiry',
                'last_contact_at' => now(),
            ]
        );

        // 2. Create Lead for this Listing
        $lead = Lead::create([
            'organization_id' => $orgId,
            'contact_id' => $contact->id,
            'listing_id' => $listing->id,
            'category_id' => $listing->category_id,
            'title' => "Inquiry for {$listing->title}",
            'source' => 'listing_inquiry',
            'status' => Lead::STATUS_NEW,
            'priority' => Lead::PRIORITY_HIGH,
            'estimated_value' => $listing->price,
            'currency' => $listing->currency,
            'notes' => $validated['message'],
            'last_contact_at' => now(),
            'next_follow_up_at' => now()->addDay(),
        ]);

        $listing->increment('inquiries_count');

        // 3. Log Activity on Contact Timeline
        CrmActivity::create([
            'organization_id' => $orgId,
            'contact_id' => $contact->id,
            'lead_id' => $lead->id,
            'type' => CrmActivity::TYPE_MESSAGE,
            'subject' => "Customer Inquiry on '{$listing->title}'",
            'description' => $validated['message'],
            'occurred_at' => now(),
        ]);

        // 4. Calculate Assistive AI Lead Score
        app(AIService::class)->scoreLead($lead);

        // 5. Fire Automation Rule (e.g. auto-assign agent, send notification)
        app(AutomationService::class)->handleTrigger(
            'lead.created',
            $orgId,
            ['lead' => $lead, 'contact' => $contact, 'listing' => $listing]
        );

        return back()->with('success', 'Your inquiry has been submitted! Our sales team will reach out to you shortly.');
    }

    public function bookAppointment(Request $request, Listing $listing)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string',
            'appointment_type' => 'required|string|in:viewing,test_drive,meeting,inspection',
            'start_time' => 'required|date|after:now',
            'notes' => 'nullable|string',
        ]);

        $orgId = $listing->organization_id;

        $nameParts = explode(' ', trim($validated['name']), 2);
        $contact = Contact::withoutGlobalScope(TenantScope::class)->firstOrCreate(
            ['organization_id' => $orgId, 'email' => $validated['email']],
            [
                'first_name' => $nameParts[0],
                'last_name' => $nameParts[1] ?? '',
                'phone' => $validated['phone'],
                'contact_type' => Contact::TYPE_LEAD,
                'status' => Contact::STATUS_PROSPECT,
                'source' => 'appointment_booking',
            ]
        );

        $appointment = Appointment::create([
            'organization_id' => $orgId,
            'contact_id' => $contact->id,
            'listing_id' => $listing->id,
            'title' => ucfirst($validated['appointment_type']) . " - {$listing->title}",
            'type' => $validated['appointment_type'],
            'status' => Appointment::STATUS_REQUESTED,
            'start_time' => $validated['start_time'],
            'end_time' => \Carbon\Carbon::parse($validated['start_time'])->addHour(),
            'notes' => $validated['notes'] ?? null,
            'location' => $listing->address ?: 'Dealership showroom',
        ]);

        CrmActivity::create([
            'organization_id' => $orgId,
            'contact_id' => $contact->id,
            'type' => CrmActivity::TYPE_APPOINTMENT,
            'subject' => "Appointment Requested: {$appointment->title}",
            'description' => "Scheduled for " . $appointment->start_time->format('Y-m-d H:i'),
            'occurred_at' => now(),
        ]);

        return back()->with('success', 'Appointment request submitted! We will confirm your time slot shortly.');
    }

    public function pricing()
    {
        $plans = \App\Models\SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();
        return view('marketplace.pricing', compact('plans'));
    }
}
