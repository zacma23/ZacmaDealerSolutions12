<?php

namespace App\Http\Controllers;

use App\Models\AiRequest;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\CategoryField;
use App\Models\CategoryFieldOption;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Listing;
use App\Models\Order;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\Scopes\TenantScope;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_organizations' => Organization::count(),
            'active_organizations' => Organization::where('status', 'active')->count(),
            'total_users' => User::count(),
            'total_listings' => Listing::withoutGlobalScope(TenantScope::class)->count(),
            'total_contacts' => Contact::withoutGlobalScope(TenantScope::class)->count(),
            'total_leads' => Lead::withoutGlobalScope(TenantScope::class)->count(),
            'total_deals_value' => Deal::withoutGlobalScope(TenantScope::class)->sum('value'),
            'total_revenue' => Payment::where('status', Payment::STATUS_VERIFIED)->sum('amount'),
            'ai_requests_count' => AiRequest::count(),
        ];

        $recentOrganizations = Organization::with('plan')->latest()->take(5)->get();
        $recentPayments = Payment::with('organization')->where('status', Payment::STATUS_VERIFIED)->latest()->take(5)->get();
        $recentLogs = AuditLog::with(['user', 'organization'])->latest()->take(8)->get();

        return view('super-admin.dashboard', compact('stats', 'recentOrganizations', 'recentPayments', 'recentLogs'));
    }

    // ==========================================
    // ORGANIZATIONS MANAGEMENT
    // ==========================================

    public function organizations(Request $request)
    {
        $query = Organization::with(['plan', 'users']);
        if ($request->filled('q')) {
            $query->where('name', 'like', "%{$request->q}%")->orWhere('slug', 'like', "%{$request->q}%");
        }
        $organizations = $query->latest()->paginate(15);
        $plans = SubscriptionPlan::where('is_active', true)->get();

        return view('super-admin.organizations.index', compact('organizations', 'plans'));
    }

    public function storeOrganization(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'city' => 'nullable|string',
            'country' => 'nullable|string',
            'currency' => 'required|string|max:3',
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8',
        ]);

        $slug = Str::slug($validated['name']);
        $count = Organization::where('slug', 'like', "{$slug}%")->count();
        if ($count > 0) $slug .= '-' . ($count + 1);

        $org = Organization::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'subdomain' => $slug,
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'city' => $validated['city'] ?? 'Addis Ababa',
            'country' => $validated['country'] ?? 'Ethiopia',
            'currency' => $validated['currency'],
            'subscription_plan_id' => $validated['subscription_plan_id'],
            'status' => 'active',
            'subscription_ends_at' => now()->addMonth(),
        ]);

        // Create Org Admin User
        $user = User::create([
            'organization_id' => $org->id,
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'password' => Hash::make($validated['admin_password']),
            'role' => User::ROLE_ORGANIZATION_ADMIN,
            'is_active' => true,
        ]);

        // Create default CRM stages for this organization
        $defaultStages = [
            ['name' => 'New', 'slug' => 'new', 'win_probability' => 10, 'color' => '#3B82F6', 'sort_order' => 1],
            ['name' => 'Contacted', 'slug' => 'contacted', 'win_probability' => 25, 'color' => '#8B5CF6', 'sort_order' => 2],
            ['name' => 'Qualified', 'slug' => 'qualified', 'win_probability' => 50, 'color' => '#EAB308', 'sort_order' => 3],
            ['name' => 'Proposal', 'slug' => 'proposal', 'win_probability' => 70, 'color' => '#F97316', 'sort_order' => 4],
            ['name' => 'Negotiation', 'slug' => 'negotiation', 'win_probability' => 85, 'color' => '#EC4899', 'sort_order' => 5],
            ['name' => 'Won', 'slug' => 'won', 'win_probability' => 100, 'color' => '#10B981', 'sort_order' => 6, 'is_closed_won' => true],
            ['name' => 'Lost', 'slug' => 'lost', 'win_probability' => 0, 'color' => '#EF4444', 'sort_order' => 7, 'is_closed_lost' => true],
        ];

        foreach ($defaultStages as $stage) {
            $org->dealStages()->create($stage);
        }

        AuditLog::log('super_admin.create_organization', $org, null, ['name' => $org->name]);

        return back()->with('success', "Organization '{$org->name}' and Administrator created successfully!");
    }

    public function toggleOrganizationStatus(Organization $organization)
    {
        $newStatus = $organization->status === 'active' ? 'suspended' : 'active';
        $organization->update(['status' => $newStatus]);

        AuditLog::log('super_admin.toggle_org_status', $organization, null, ['status' => $newStatus]);

        return back()->with('success', "Organization status updated to '{$newStatus}'.");
    }

    // ==========================================
    // DYNAMIC CATEGORY & FIELD BUILDER
    // ==========================================

    public function categories()
    {
        $categories = Category::with(['fields.options', 'parent'])->whereNull('parent_id')->orderBy('sort_order')->get();
        return view('super-admin.categories.index', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $category = Category::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'icon' => $validated['icon'] ?? 'fas fa-layer-group',
            'description' => $validated['description'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'is_active' => true,
        ]);

        AuditLog::log('super_admin.create_category', $category);

        return back()->with('success', "Category '{$category->name}' created successfully!");
    }

    public function storeCategoryField(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|regex:/^[a-z0-9_]+$/|max:50',
            'label' => 'required|string|max:100',
            'field_type' => 'required|string|in:text,number,select,multiselect,date,boolean,textarea,currency',
            'unit' => 'nullable|string|max:20',
            'is_required' => 'nullable|boolean',
            'is_filterable' => 'nullable|boolean',
            'show_in_card' => 'nullable|boolean',
            'options' => 'nullable|string', // comma-separated options for select/multiselect
        ]);

        $field = $category->fields()->create([
            'name' => $validated['name'],
            'label' => $validated['label'],
            'field_type' => $validated['field_type'],
            'unit' => $validated['unit'] ?? null,
            'is_required' => $request->boolean('is_required'),
            'is_filterable' => $request->boolean('is_filterable', true),
            'show_in_card' => $request->boolean('show_in_card'),
            'sort_order' => $category->fields()->count() + 1,
        ]);

        if (in_array($validated['field_type'], ['select', 'multiselect']) && !empty($validated['options'])) {
            $optionsList = explode(',', $validated['options']);
            foreach ($optionsList as $idx => $opt) {
                $trimmed = trim($opt);
                if ($trimmed) {
                    $field->options()->create([
                        'label' => $trimmed,
                        'value' => Str::slug($trimmed),
                        'sort_order' => $idx + 1,
                    ]);
                }
            }
        }

        AuditLog::log('super_admin.create_category_field', $field);

        return back()->with('success', "Custom field '{$field->label}' added to category '{$category->name}'.");
    }

    // ==========================================
    // PLANS & LIMITS MANAGEMENT
    // ==========================================

    public function plans()
    {
        $plans = SubscriptionPlan::withCount('organizations')->orderBy('price')->get();
        return view('super-admin.plans.index', compact('plans'));
    }

    public function storePlan(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'interval' => 'required|string|in:monthly,yearly',
            'listing_limit' => 'required|integer|min:1',
            'contact_limit' => 'required|integer|min:1',
            'user_limit' => 'required|integer|min:1',
            'ai_request_limit' => 'required|integer|min:0',
        ]);

        $plan = SubscriptionPlan::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'price' => $validated['price'],
            'currency' => $validated['currency'],
            'interval' => $validated['interval'],
            'listing_limit' => $validated['listing_limit'],
            'contact_limit' => $validated['contact_limit'],
            'user_limit' => $validated['user_limit'],
            'ai_request_limit' => $validated['ai_request_limit'],
            'is_active' => true,
        ]);

        AuditLog::log('super_admin.create_plan', $plan);

        return back()->with('success', "Subscription Plan '{$plan->name}' created successfully!");
    }

    // ==========================================
    // GLOBAL CRM & LISTINGS
    // ==========================================

    public function globalCrm()
    {
        $leads = Lead::withoutGlobalScope(TenantScope::class)->with(['organization', 'contact', 'listing'])->latest()->paginate(15);
        $contacts = Contact::withoutGlobalScope(TenantScope::class)->with('organization')->latest()->paginate(15);
        $deals = Deal::withoutGlobalScope(TenantScope::class)->with(['organization', 'contact', 'stage'])->latest()->paginate(15);

        return view('super-admin.crm.index', compact('leads', 'contacts', 'deals'));
    }

    public function globalListings()
    {
        $listings = Listing::withoutGlobalScope(TenantScope::class)->with(['organization', 'category', 'user'])->latest()->paginate(15);
        return view('super-admin.listings.index', compact('listings'));
    }

    // ==========================================
    // SETTINGS & INTEGRATIONS
    // ==========================================

    public function settings()
    {
        $settings = Setting::whereNull('organization_id')->get()->pluck('value', 'key');
        return view('super-admin.settings.index', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $allowed = [
            'platform_name', 'support_email', 'gemini_api_key', 'gemini_model',
            'santimpay_merchant_id', 'santimpay_private_key', 'telebirr_app_id',
            'telebirr_app_key', 'telebirr_short_code', 'chapa_secret_key',
            'paypal_client_id', 'paypal_secret', 'stripe_secret_key'
        ];

        foreach ($request->only($allowed) as $key => $val) {
            Setting::set($key, $val, 'integrations', null);
        }

        AuditLog::log('super_admin.update_settings');

        return back()->with('success', 'Platform settings and integration keys updated successfully!');
    }

    public function auditLogs()
    {
        $logs = AuditLog::with(['organization', 'user'])->latest()->paginate(30);
        return view('super-admin.audit-logs.index', compact('logs'));
    }
}
