<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Category;
use App\Models\Contact;
use App\Models\CrmActivity;
use App\Models\Deal;
use App\Models\DealStage;
use App\Models\Lead;
use App\Models\Listing;
use App\Models\ListingFieldValue;
use App\Models\ListingMedia;
use App\Models\Note;
use App\Models\Order;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\Task;
use App\Models\User;
use App\Services\AI\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DealerPortalController extends Controller
{
    public function dashboard()
    {
        $org = Auth::user()->organization;

        $stats = [
            'total_leads' => Lead::count(),
            'new_leads' => Lead::where('status', Lead::STATUS_NEW)->count(),
            'hot_leads' => Lead::where('score_category', Lead::SCORE_HOT)->count(),
            'total_contacts' => Contact::count(),
            'pipeline_value' => Deal::sum('value'),
            'open_deals_count' => Deal::whereHas('stage', fn($q) => $q->where('is_closed_won', false)->where('is_closed_lost', false))->count(),
            'follow_ups_due' => Task::where('status', 'pending')->where('due_date', '<=', now()->endOfDay())->count(),
            'total_listings' => Listing::count(),
            'revenue_month' => Payment::where('status', Payment::STATUS_VERIFIED)->whereMonth('created_at', now()->month)->sum('amount'),
        ];

        $recentLeads = Lead::with(['contact', 'listing'])->latest()->take(6)->get();
        $recentActivities = CrmActivity::with(['contact', 'user'])->latest('occurred_at')->take(8)->get();
        $upcomingAppointments = Appointment::with(['contact', 'listing'])->where('start_time', '>=', now())->orderBy('start_time')->take(5)->get();

        return view('dealer.dashboard', compact('org', 'stats', 'recentLeads', 'recentActivities', 'upcomingAppointments'));
    }

    // ==========================================
    // CRM CONTACTS & CUSTOMER 360
    // ==========================================

    public function contacts(Request $request)
    {
        $query = Contact::with(['assignedUser', 'leads', 'deals']);
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($b) use ($q) {
                $b->where('first_name', 'like', "%{$q}%")
                  ->orWhere('last_name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('phone', 'like', "%{$q}%")
                  ->orWhere('company', 'like', "%{$q}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('contact_type', $request->type);
        }

        $contacts = $query->latest()->paginate(15);
        $staff = User::where('organization_id', Auth::user()->organization_id)->where('is_active', true)->get();

        return view('dealer.crm.contacts.index', compact('contacts', 'staff'));
    }

    public function storeContact(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'company' => 'nullable|string|max:150',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:30',
            'contact_type' => 'required|string',
            'status' => 'required|string',
            'source' => 'nullable|string',
            'assigned_user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $contact = Contact::create($validated);

        return back()->with('success', "Contact '{$contact->full_name}' created successfully!");
    }

    public function showContact360(Contact $contact)
    {
        $contact->load([
            'assignedUser',
            'leads.listing',
            'deals.stage',
            'activities.user',
            'tasks.assignedUser',
            'appointments.listing',
            'orders.items',
            'notes.user',
        ]);

        $stages = DealStage::orderBy('sort_order')->get();
        $staff = User::where('organization_id', Auth::user()->organization_id)->where('is_active', true)->get();
        $listings = Listing::where('status', Listing::STATUS_PUBLISHED)->get();

        return view('dealer.crm.contacts.show_360', compact('contact', 'stages', 'staff', 'listings'));
    }

    public function generateAiSummary(Contact $contact, AIService $aiService)
    {
        $summary = $aiService->summarizeContact($contact);
        return back()->with('success', 'AI Summary generated successfully!')->with('ai_summary', $summary);
    }

    public function draftAiCommunication(Request $request, Contact $contact, AIService $aiService)
    {
        $channel = $request->get('channel', 'sms');
        $instruction = $request->get('instruction');
        $draft = $aiService->draftCommunication($channel, $contact, $instruction);

        return back()->with('draft_content', $draft)->with('draft_channel', $channel);
    }

    // ==========================================
    // CRM LEADS & SCORING
    // ==========================================

    public function leads(Request $request)
    {
        $query = Lead::with(['contact', 'listing', 'assignedUser']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('score_category')) {
            $query->where('score_category', $request->score_category);
        }
        if ($request->filled('assigned_to')) {
            $query->where('assigned_user_id', $request->assigned_to);
        }

        $leads = $query->latest()->paginate(15);
        $staff = User::where('organization_id', Auth::user()->organization_id)->where('is_active', true)->get();

        return view('dealer.crm.leads.index', compact('leads', 'staff'));
    }

    public function updateLeadStatus(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'assigned_user_id' => 'nullable|exists:users,id',
            'priority' => 'nullable|string',
        ]);

        $lead->update($validated);

        return back()->with('success', 'Lead updated successfully.');
    }

    public function rescoreLead(Lead $lead, AIService $aiService)
    {
        $score = $aiService->scoreLead($lead);
        return back()->with('success', "Lead rescored: {$score['score']}/100 ({$score['category']})");
    }

    // ==========================================
    // VISUAL KANBAN SALES PIPELINE & DEALS
    // ==========================================

    public function pipeline()
    {
        $stages = DealStage::with(['deals' => function ($q) {
            $q->with(['contact', 'listing', 'assignedUser'])->latest();
        }])->orderBy('sort_order')->get();

        $staff = User::where('organization_id', Auth::user()->organization_id)->where('is_active', true)->get();
        $contacts = Contact::all();
        $listings = Listing::where('status', Listing::STATUS_PUBLISHED)->get();

        return view('dealer.crm.pipeline.index', compact('stages', 'staff', 'contacts', 'listings'));
    }

    public function storeDeal(Request $request)
    {
        $validated = $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'deal_stage_id' => 'required|exists:deal_stages,id',
            'listing_id' => 'nullable|exists:listings,id',
            'title' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'probability' => 'required|integer|min:0|max:100',
            'expected_close_date' => 'nullable|date',
            'assigned_user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $deal = Deal::create($validated);

        CrmActivity::create([
            'organization_id' => Auth::user()->organization_id,
            'contact_id' => $deal->contact_id,
            'deal_id' => $deal->id,
            'type' => CrmActivity::TYPE_QUOTE,
            'subject' => "New Deal Created: {$deal->title}",
            'description' => "Value: {$deal->currency} {$deal->value}",
            'occurred_at' => now(),
        ]);

        return back()->with('success', "Deal '{$deal->title}' added to pipeline!");
    }

    public function moveDealStage(Request $request, Deal $deal)
    {
        $validated = $request->validate([
            'deal_stage_id' => 'required|exists:deal_stages,id',
        ]);

        $oldStageName = $deal->stage?->name;
        $deal->update(['deal_stage_id' => $validated['deal_stage_id']]);
        $newStage = DealStage::find($validated['deal_stage_id']);

        CrmActivity::create([
            'organization_id' => Auth::user()->organization_id,
            'contact_id' => $deal->contact_id,
            'deal_id' => $deal->id,
            'type' => CrmActivity::TYPE_NOTE,
            'subject' => "Stage Moved to {$newStage->name}",
            'description' => "Deal was transitioned from '{$oldStageName}' to '{$newStage->name}'.",
            'occurred_at' => now(),
        ]);

        return back()->with('success', "Deal moved to '{$newStage->name}'!");
    }

    // ==========================================
    // TASKS, FOLLOW-UPS & CALENDAR
    // ==========================================

    public function tasks(Request $request)
    {
        $filter = $request->get('filter', 'all');

        $query = Task::with(['contact', 'assignedUser']);

        if ($filter === 'overdue') {
            $query->where('status', '!=', 'completed')->where('due_date', '<', now());
        } elseif ($filter === 'today') {
            $query->where('status', '!=', 'completed')->whereDate('due_date', now()->today());
        } elseif ($filter === 'upcoming') {
            $query->where('status', '!=', 'completed')->where('due_date', '>', now()->endOfDay());
        }

        $tasks = $query->orderBy('due_date')->paginate(15);
        $contacts = Contact::all();
        $staff = User::where('organization_id', Auth::user()->organization_id)->get();

        return view('dealer.crm.tasks.index', compact('tasks', 'contacts', 'staff', 'filter'));
    }

    public function storeTask(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'contact_id' => 'nullable|exists:contacts,id',
            'assigned_user_id' => 'nullable|exists:users,id',
            'priority' => 'required|string',
            'due_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $task = Task::create(array_merge($validated, ['status' => 'pending']));

        return back()->with('success', 'Task scheduled successfully!');
    }

    public function toggleTask(Task $task)
    {
        $newStatus = $task->status === 'completed' ? 'pending' : 'completed';
        $task->update([
            'status' => $newStatus,
            'completed_at' => $newStatus === 'completed' ? now() : null,
        ]);

        return back()->with('success', 'Task status updated.');
    }

    public function calendar()
    {
        $appointments = Appointment::with(['contact', 'listing', 'assignedUser'])
            ->orderBy('start_time')
            ->get();

        $tasks = Task::whereNotNull('due_date')->with('contact')->get();

        return view('dealer.crm.calendar.index', compact('appointments', 'tasks'));
    }

    // ==========================================
    // AI CRM ASSISTANT
    // ==========================================

    public function aiAssistant(Request $request, AIService $aiService)
    {
        $response = null;
        $query = $request->get('query');

        if ($query) {
            $response = $aiService->queryAssistant($query, Auth::user()->organization_id, Auth::user());
        }

        return view('dealer.ai.assistant', compact('response', 'query'));
    }

    // ==========================================
    // INVENTORY / LISTINGS
    // ==========================================

    public function listings()
    {
        $listings = Listing::with(['category', 'primaryMedia'])->latest()->paginate(15);
        return view('dealer.listings.index', compact('listings'));
    }

    public function createListing()
    {
        $org = Auth::user()->organization;
        $categories = Category::with('fields.options')->whereNull('parent_id')->get();
        $canPostToday = $org ? $org->canPostItemToday() : true;
        $dailyPostsAllowed = $org ? $org->dailyPostsAllowed() : -1;
        $postsTodayCount = $org ? $org->postsTodayCount() : 0;

        return view('dealer.listings.create', compact('categories', 'org', 'canPostToday', 'dailyPostsAllowed', 'postsTodayCount'));
    }

    public function aiDetectListing(Request $request, AIService $aiService)
    {
        $request->validate([
            'image' => 'nullable|file|image|max:10240',
            'hints' => 'nullable|string|max:500',
            'category_id' => 'nullable|integer',
        ]);

        $image = $request->file('image');
        $hints = $request->input('hints');
        $categoryId = $request->input('category_id');

        $detection = $aiService->detectListingFromImage(
            image: $image,
            hints: $hints,
            preferredCategoryId: $categoryId ? (int) $categoryId : null,
            orgId: Auth::user()->organization_id,
            user: Auth::user()
        );

        return response()->json([
            'success' => true,
            'data' => $detection,
        ]);
    }

    public function storeListing(Request $request)
    {
        $org = Auth::user()->organization;
        if ($org && !$org->canPostItemToday()) {
            $limit = $org->dailyPostsAllowed();
            return back()->withInput()->with('error', "Daily posting limit reached ({$org->postsTodayCount()}/{$limit} items today on your {$org->plan?->name} Plan). Please upgrade your subscription to post more items today.");
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'price_type' => 'required|string',
            'city' => 'nullable|string',
            'address' => 'nullable|string',
            'contact_phone' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'fields' => 'nullable|array',
            'images.*' => 'nullable|image|max:5120', // max 5MB
        ]);

        $slug = Str::slug($validated['title']) . '-' . uniqid();
        $orgId = Auth::user()->organization_id ?? \App\Models\Organization::first()?->id;

        $listing = Listing::create([
            'organization_id' => $orgId,
            'user_id' => Auth::id(),
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'currency' => $validated['currency'],
            'price_type' => $validated['price_type'],
            'city' => $validated['city'] ?? 'Addis Ababa',
            'address' => $validated['address'] ?? null,
            'contact_phone' => $validated['contact_phone'] ?? Auth::user()->phone,
            'contact_email' => $validated['contact_email'] ?? Auth::user()->email,
            'status' => Listing::STATUS_PUBLISHED,
            'approval_status' => 'approved',
        ]);

        // Dynamic Field Values
        if (!empty($validated['fields'])) {
            foreach ($validated['fields'] as $fieldId => $val) {
                if ($val !== null && $val !== '') {
                    $stringVal = is_array($val) ? json_encode($val) : (string) $val;
                    $numericVal = is_numeric($val) ? (float) $val : null;

                    ListingFieldValue::create([
                        'listing_id' => $listing->id,
                        'category_field_id' => $fieldId,
                        'value' => $stringVal,
                        'numeric_value' => $numericVal,
                    ]);
                }
            }
        }

        // Upload Photos
        if ($request->hasFile('images')) {
            $isFirst = true;
            foreach ($request->file('images') as $file) {
                $path = $file->store("organizations/{$listing->organization_id}/listings", 'public');
                ListingMedia::create([
                    'listing_id' => $listing->id,
                    'file_path' => $path,
                    'original_filename' => $file->getClientOriginalName(),
                    'media_type' => 'image',
                    'file_size_kb' => (int) ($file->getSize() / 1024),
                    'is_primary' => $isFirst,
                ]);
                $isFirst = false;
            }
        }

        return redirect()->route('dealer.listings.index')->with('success', "Listing '{$listing->title}' published successfully!");
    }

    // ==========================================
    // ORDERS & PAYMENTS
    // ==========================================

    public function orders()
    {
        $orders = Order::with(['contact', 'listing', 'payments'])->latest()->paginate(15);
        return view('dealer.orders.index', compact('orders'));
    }

    // ==========================================
    // STAFF MANAGEMENT
    // ==========================================

    public function staff()
    {
        $staff = User::where('organization_id', Auth::user()->organization_id)->latest()->get();
        return view('dealer.staff.index', compact('staff'));
    }

    public function storeStaff(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string',
            'role' => 'required|string|in:MANAGER,SALES_AGENT,STAFF',
            'password' => 'required|string|min:8',
        ]);

        User::create([
            'organization_id' => Auth::user()->organization_id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        return back()->with('success', "Staff member '{$validated['name']}' added successfully!");
    }

    // ==========================================
    // BRANDING & SETTINGS
    // ==========================================

    public function settings()
    {
        $org = Auth::user()->organization;
        return view('dealer.settings.index', compact('org'));
    }

    public function updateSettings(Request $request)
    {
        $org = Auth::user()->organization;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'currency' => 'required|string|max:3',
            'primary_color' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
        ]);

        $colors = $org->branding_colors ?? [];
        if ($request->filled('primary_color')) {
            $colors['primary'] = $request->primary_color;
        }

        $data = [
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'currency' => $validated['currency'],
            'branding_colors' => $colors,
        ];

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store("organizations/{$org->id}/branding", 'public');
        }

        $org->update($data);

        return back()->with('success', 'Organization branding and profile updated successfully!');
    }

    // ==========================================
    // SUBSCRIPTION & PLANS
    // ==========================================

    public function subscription()
    {
        $user = Auth::user();
        $org = $user->organization;

        if (!$org) {
            $org = \App\Models\Organization::first();
            if ($org && $user->organization_id === null && !$user->isSuperAdmin()) {
                $user->update(['organization_id' => $org->id]);
            }
        }

        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();
        $postsToday = $org ? $org->postsTodayCount() : 0;
        $dailyLimit = $org ? $org->dailyPostsAllowed() : 5;

        return view('dealer.subscription.index', compact('org', 'plans', 'postsToday', 'dailyLimit'));
    }

    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        return redirect()->route('checkout.subscription.show', $validated['plan_id']);
    }
}
