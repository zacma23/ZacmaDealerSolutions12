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
            'total_customers' => User::where('role', User::ROLE_CUSTOMER)->count(),
            'total_dealers' => Organization::count(),
            'total_sales_agents' => User::where('role', User::ROLE_SALES_AGENT)->count(),
            'total_listings' => Listing::withoutGlobalScope(TenantScope::class)->count(),
            'auto_listings' => Listing::withoutGlobalScope(TenantScope::class)->whereHas('category', fn($q) => $q->where('slug', 'vehicles'))->count(),
            'property_listings' => Listing::withoutGlobalScope(TenantScope::class)->whereHas('category', fn($q) => $q->where('slug', 'property'))->count(),
            'electronics_listings' => Listing::withoutGlobalScope(TenantScope::class)->whereHas('category', fn($q) => $q->where('slug', 'electronics'))->count(),
            'active_listings' => Listing::withoutGlobalScope(TenantScope::class)->where('status', Listing::STATUS_PUBLISHED)->count(),
            'pending_listings' => Listing::withoutGlobalScope(TenantScope::class)->where('approval_status', 'pending')->count(),
            'total_contacts' => Contact::withoutGlobalScope(TenantScope::class)->count(),
            'total_leads' => Lead::withoutGlobalScope(TenantScope::class)->count(),
            'total_orders' => Order::withoutGlobalScope(TenantScope::class)->count(),
            'total_deals_value' => Deal::withoutGlobalScope(TenantScope::class)->sum('value'),
            'total_revenue' => Payment::where('status', Payment::STATUS_VERIFIED)->sum('amount'),
            'ai_requests_count' => AiRequest::count(),
        ];

        $recentOrganizations = Organization::with('plan')->latest()->take(5)->get();
        $recentPayments = Payment::with('organization')->where('status', Payment::STATUS_VERIFIED)->latest()->take(5)->get();
        $recentListings = Listing::withoutGlobalScope(TenantScope::class)->with(['organization', 'category'])->latest()->take(5)->get();
        $recentLeads = Lead::withoutGlobalScope(TenantScope::class)->with(['organization', 'contact'])->latest()->take(5)->get();
        $recentLogs = AuditLog::with(['user', 'organization'])->latest()->take(8)->get();

        return view('super-admin.dashboard', compact('stats', 'recentOrganizations', 'recentPayments', 'recentListings', 'recentLeads', 'recentLogs'));
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

    public function globalListings(Request $request)
    {
        $query = Listing::withoutGlobalScope(TenantScope::class)->with(['organization', 'category', 'user', 'primaryMedia']);
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $query->where('title', 'like', "%{$request->q}%");
        }
        $listings = $query->latest()->paginate(15);
        $categories = Category::all();
        return view('super-admin.listings.index', compact('listings', 'categories'));
    }

    // ==========================================
    // INDUSTRY SECTORS: AUTO, REAL ESTATE, ELECTRONICS
    // ==========================================

    public function auto(Request $request)
    {
        $category = Category::where('slug', 'vehicles')->first();
        $query = Listing::withoutGlobalScope(TenantScope::class)
            ->where('category_id', $category?->id)
            ->with(['organization', 'user', 'fieldValues.categoryField', 'media']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $query->where('title', 'like', "%{$request->q}%");
        }

        $listings = $query->latest()->paginate(15);
        $totalVehicles = Listing::withoutGlobalScope(TenantScope::class)->where('category_id', $category?->id)->count();
        $activeVehicles = Listing::withoutGlobalScope(TenantScope::class)->where('category_id', $category?->id)->where('status', Listing::STATUS_PUBLISHED)->count();

        return view('super-admin.auto.index', compact('listings', 'totalVehicles', 'activeVehicles', 'category'));
    }

    public function property(Request $request)
    {
        $category = Category::where('slug', 'property')->first();
        $query = Listing::withoutGlobalScope(TenantScope::class)
            ->where('category_id', $category?->id)
            ->with(['organization', 'user', 'fieldValues.categoryField', 'media']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $query->where('title', 'like', "%{$request->q}%");
        }

        $listings = $query->latest()->paginate(15);
        $totalProperties = Listing::withoutGlobalScope(TenantScope::class)->where('category_id', $category?->id)->count();
        $activeProperties = Listing::withoutGlobalScope(TenantScope::class)->where('category_id', $category?->id)->where('status', Listing::STATUS_PUBLISHED)->count();

        return view('super-admin.property.index', compact('listings', 'totalProperties', 'activeProperties', 'category'));
    }

    public function electronics(Request $request)
    {
        $category = Category::where('slug', 'electronics')->first();
        $query = Listing::withoutGlobalScope(TenantScope::class)
            ->where('category_id', $category?->id)
            ->with(['organization', 'user', 'fieldValues.categoryField', 'media']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $query->where('title', 'like', "%{$request->q}%");
        }

        $listings = $query->latest()->paginate(15);
        $totalElectronics = Listing::withoutGlobalScope(TenantScope::class)->where('category_id', $category?->id)->count();
        $activeElectronics = Listing::withoutGlobalScope(TenantScope::class)->where('category_id', $category?->id)->where('status', Listing::STATUS_PUBLISHED)->count();

        return view('super-admin.electronics.index', compact('listings', 'totalElectronics', 'activeElectronics', 'category'));
    }

    // ==========================================
    // LISTING MODERATION ACTIONS
    // ==========================================

    public function approveListing(Listing $listing)
    {
        $listing->update([
            'approval_status' => 'approved',
            'status' => Listing::STATUS_PUBLISHED,
        ]);
        AuditLog::log('super_admin.approve_listing', $listing);
        return back()->with('success', "Listing '{$listing->title}' has been approved and published!");
    }

    public function rejectListing(Listing $listing)
    {
        $listing->update([
            'approval_status' => 'rejected',
            'status' => Listing::STATUS_DRAFT,
        ]);
        AuditLog::log('super_admin.reject_listing', $listing);
        return back()->with('success', "Listing '{$listing->title}' has been rejected.");
    }

    public function toggleFeatureListing(Listing $listing)
    {
        $listing->update(['featured' => !$listing->featured]);
        AuditLog::log('super_admin.toggle_featured_listing', $listing);
        $status = $listing->featured ? 'featured' : 'unfeatured';
        return back()->with('success', "Listing is now {$status} on marketplace.");
    }

    public function destroyListing(Listing $listing)
    {
        $title = $listing->title;
        $listing->delete();
        AuditLog::log('super_admin.delete_listing', null, null, ['title' => $title]);
        return back()->with('success', "Listing '{$title}' removed successfully.");
    }

    // ==========================================
    // USERS, CUSTOMERS & SALES AGENTS
    // ==========================================

    public function users(Request $request)
    {
        $query = User::with('organization');
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->q}%")
                  ->orWhere('email', 'like', "%{$request->q}%")
                  ->orWhere('phone', 'like', "%{$request->q}%");
            });
        }
        $users = $query->latest()->paginate(20);
        return view('super-admin.users.index', compact('users'));
    }

    public function toggleUserStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Cannot deactivate your own super admin account.');
        }
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'activated' : 'deactivated';
        AuditLog::log('super_admin.toggle_user_status', $user);
        return back()->with('success', "User account {$user->name} {$status}.");
    }

    public function customers(Request $request)
    {
        $query = User::where('role', User::ROLE_CUSTOMER)
            ->withCount(['orders'])
            ->with('organization');

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->q}%")
                  ->orWhere('email', 'like', "%{$request->q}%")
                  ->orWhere('phone', 'like', "%{$request->q}%");
            });
        }

        $customers = $query->latest()->paginate(15);
        $totalCustomers = User::where('role', User::ROLE_CUSTOMER)->count();
        $totalOrders = Order::withoutGlobalScope(TenantScope::class)->count();

        return view('super-admin.customers.index', compact('customers', 'totalCustomers', 'totalOrders'));
    }

    public function salesAgents(Request $request)
    {
        $query = User::where('role', User::ROLE_SALES_AGENT)
            ->with('organization')
            ->withCount(['assignedLeads', 'assignedDeals']);

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->q}%")
                  ->orWhere('email', 'like', "%{$request->q}%");
            });
        }

        $agents = $query->latest()->paginate(15);
        $totalAgents = User::where('role', User::ROLE_SALES_AGENT)->count();

        return view('super-admin.sales-agents.index', compact('agents', 'totalAgents'));
    }

    // ==========================================
    // SALES, ORDERS & PAYMENTS
    // ==========================================

    public function orders(Request $request)
    {
        $query = Order::withoutGlobalScope(TenantScope::class)->with(['organization', 'user', 'listing', 'items', 'payment']);
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('order_number', 'like', "%{$request->q}%")
                  ->orWhere('customer_name', 'like', "%{$request->q}%");
            });
        }
        $orders = $query->latest()->paginate(15);
        $totalRevenue = Order::withoutGlobalScope(TenantScope::class)->where('payment_status', Order::PAYMENT_PAID)->sum('total_amount');

        return view('super-admin.orders.index', compact('orders', 'totalRevenue'));
    }

    public function payments(Request $request)
    {
        $query = Payment::withoutGlobalScope(TenantScope::class)->with(['organization', 'order']);
        if ($request->filled('provider')) {
            $query->where('provider', $request->provider);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $payments = $query->latest()->paginate(15);
        $totalVerified = Payment::withoutGlobalScope(TenantScope::class)->where('status', Payment::STATUS_VERIFIED)->sum('amount');

        return view('super-admin.payments.index', compact('payments', 'totalVerified'));
    }

    // ==========================================
    // IMPERSONATION / "VIEW AS"
    // ==========================================

    public function impersonate(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Cannot impersonate your own active session.');
        }

        $superAdminId = auth()->id();
        AuditLog::log('super_admin.impersonate', $user, null, ['target_user' => $user->email, 'target_role' => $user->role]);

        auth()->login($user);
        session(['impersonator_id' => $superAdminId]);

        return redirect($user->getDashboardUrl())->with('info', "Now viewing platform as {$user->name} ({$user->role}).");
    }

    public function stopImpersonation()
    {
        $superAdminId = session('impersonator_id');
        if (!$superAdminId) {
            return redirect()->route('home');
        }

        $superAdmin = User::find($superAdminId);
        if ($superAdmin && $superAdmin->isSuperAdmin()) {
            auth()->login($superAdmin);
            session()->forget('impersonator_id');
            AuditLog::log('super_admin.stop_impersonation', $superAdmin);
            return redirect()->route('super-admin.dashboard')->with('success', 'Restored Super Admin Command session.');
        }

        session()->forget('impersonator_id');
        return redirect()->route('home');
    }

    // ==========================================
    // GLOBAL PLATFORM SEARCH
    // ==========================================

    public function search(Request $request)
    {
        $q = trim((string) $request->q);
        $results = [
            'listings' => collect(),
            'users' => collect(),
            'organizations' => collect(),
            'leads' => collect(),
            'orders' => collect(),
        ];

        if ($q !== '') {
            $results['listings'] = Listing::withoutGlobalScope(TenantScope::class)
                ->where('title', 'like', "%{$q}%")
                ->orWhere('description', 'like', "%{$q}%")
                ->with(['category', 'organization'])
                ->take(8)->get();

            $results['users'] = User::where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('phone', 'like', "%{$q}%")
                ->with('organization')
                ->take(8)->get();

            $results['organizations'] = Organization::where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('city', 'like', "%{$q}%")
                ->take(8)->get();

            $results['leads'] = Lead::withoutGlobalScope(TenantScope::class)
                ->where('title', 'like', "%{$q}%")
                ->with(['contact', 'organization'])
                ->take(8)->get();

            $results['orders'] = Order::withoutGlobalScope(TenantScope::class)
                ->where('order_number', 'like', "%{$q}%")
                ->orWhere('customer_name', 'like', "%{$q}%")
                ->take(8)->get();
        }

        return view('super-admin.search.index', compact('q', 'results'));
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
