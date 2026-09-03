<?php

use App\Http\Controllers\AiChatController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\DealerPortalController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdminController;
use Illuminate\Support\Facades\Route;

// ==========================================
// PUBLIC & MARKETPLACE ROUTES
// ==========================================

Route::get('/', [MarketplaceController::class, 'index'])->name('home');
Route::get('/browse', [MarketplaceController::class, 'browse'])->name('marketplace.browse');
Route::get('/category/{categorySlug}', [MarketplaceController::class, 'browse'])->name('marketplace.category');
Route::get('/listing/{slug}', [MarketplaceController::class, 'show'])->name('marketplace.show');
Route::post('/listing/{listing}/inquire', [MarketplaceController::class, 'submitInquiry'])->name('marketplace.inquire');
Route::post('/listing/{listing}/book', [MarketplaceController::class, 'bookAppointment'])->name('marketplace.book');
Route::get('/pricing', [MarketplaceController::class, 'pricing'])->name('pricing');

// ==========================================
// CHECKOUT & PAYMENTS
// ==========================================

Route::get('/checkout/{listing}', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout/{listing}/pay', [CheckoutController::class, 'process'])->name('checkout.process');
Route::get('/checkout/verify/{provider}', [CheckoutController::class, 'verify'])->name('checkout.verify');
Route::get('/checkout/subscription/{plan}', [CheckoutController::class, 'showSubscriptionCheckout'])->name('checkout.subscription.show');
Route::post('/checkout/subscription/{plan}', [CheckoutController::class, 'processSubscriptionCheckout'])->name('checkout.subscription.process');
Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/checkout/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');

// ==========================================
// AUTHENTICATION ROUTES
// ==========================================

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/verify-otp', [AuthController::class, 'showVerifyOtp'])->name('otp.verify');
Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('otp.verify.submit');

// ==========================================
// USER PROFILE & ACCOUNT SETTINGS
// ==========================================

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'removeAvatar'])->name('profile.avatar.remove');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
});

// ==========================================
// SUPER ADMIN PORTAL
// ==========================================

Route::prefix('super-admin')->middleware(['auth', 'role:SUPER_ADMIN'])->group(function () {
    Route::get('/', [SuperAdminController::class, 'dashboard'])->name('super-admin.dashboard');
    
    // Organizations
    Route::get('/organizations', [SuperAdminController::class, 'organizations'])->name('super-admin.organizations.index');
    Route::post('/organizations', [SuperAdminController::class, 'storeOrganization'])->name('super-admin.organizations.store');
    Route::post('/organizations/{organization}/toggle', [SuperAdminController::class, 'toggleOrganizationStatus'])->name('super-admin.organizations.toggle');

    // Dynamic Categories & Fields
    Route::get('/categories', [SuperAdminController::class, 'categories'])->name('super-admin.categories.index');
    Route::post('/categories', [SuperAdminController::class, 'storeCategory'])->name('super-admin.categories.store');
    Route::post('/categories/{category}/fields', [SuperAdminController::class, 'storeCategoryField'])->name('super-admin.categories.fields.store');

    // Plans & Limits
    Route::get('/plans', [SuperAdminController::class, 'plans'])->name('super-admin.plans.index');
    Route::post('/plans', [SuperAdminController::class, 'storePlan'])->name('super-admin.plans.store');

    // Global CRM & Listings
    Route::get('/crm', [SuperAdminController::class, 'globalCrm'])->name('super-admin.crm.index');
    Route::get('/listings', [SuperAdminController::class, 'globalListings'])->name('super-admin.listings.index');

    // Platform Settings & Integrations
    Route::get('/settings', [SuperAdminController::class, 'settings'])->name('super-admin.settings.index');
    Route::post('/settings', [SuperAdminController::class, 'updateSettings'])->name('super-admin.settings.update');
    Route::get('/audit-logs', [SuperAdminController::class, 'auditLogs'])->name('super-admin.audit-logs.index');
});

// ==========================================
// DEALER / ORGANIZATION PORTAL
// ==========================================

Route::prefix('dealer')->middleware(['auth', 'role:ORGANIZATION_ADMIN,MANAGER,SALES_AGENT,STAFF,SELLER,CUSTOMER'])->group(function () {
    Route::get('/', [DealerPortalController::class, 'dashboard'])->name('dealer.dashboard');

    // CRM Contacts & Customer 360
    Route::get('/crm/contacts', [DealerPortalController::class, 'contacts'])->name('dealer.crm.contacts.index');
    Route::post('/crm/contacts', [DealerPortalController::class, 'storeContact'])->name('dealer.crm.contacts.store');
    Route::get('/crm/contacts/{contact}/360', [DealerPortalController::class, 'showContact360'])->name('dealer.crm.contacts.360');
    Route::post('/crm/contacts/{contact}/ai-summary', [DealerPortalController::class, 'generateAiSummary'])->name('dealer.crm.contacts.ai-summary');
    Route::post('/crm/contacts/{contact}/ai-draft', [DealerPortalController::class, 'draftAiCommunication'])->name('dealer.crm.contacts.ai-draft');

    // CRM Leads & Scoring
    Route::get('/crm/leads', [DealerPortalController::class, 'leads'])->name('dealer.crm.leads.index');
    Route::post('/crm/leads/{lead}/status', [DealerPortalController::class, 'updateLeadStatus'])->name('dealer.crm.leads.status');
    Route::post('/crm/leads/{lead}/rescore', [DealerPortalController::class, 'rescoreLead'])->name('dealer.crm.leads.rescore');

    // Sales Pipeline & Deals (Kanban)
    Route::get('/crm/pipeline', [DealerPortalController::class, 'pipeline'])->name('dealer.crm.pipeline.index');
    Route::post('/crm/deals', [DealerPortalController::class, 'storeDeal'])->name('dealer.crm.deals.store');
    Route::post('/crm/deals/{deal}/move', [DealerPortalController::class, 'moveDealStage'])->name('dealer.crm.deals.move');

    // Tasks & Calendar
    Route::get('/crm/tasks', [DealerPortalController::class, 'tasks'])->name('dealer.crm.tasks.index');
    Route::post('/crm/tasks', [DealerPortalController::class, 'storeTask'])->name('dealer.crm.tasks.store');
    Route::post('/crm/tasks/{task}/toggle', [DealerPortalController::class, 'toggleTask'])->name('dealer.crm.tasks.toggle');
    Route::get('/crm/calendar', [DealerPortalController::class, 'calendar'])->name('dealer.crm.calendar.index');

    // AI CRM Assistant
    Route::get('/ai-assistant', [DealerPortalController::class, 'aiAssistant'])->name('dealer.ai.assistant');

    // Listings / Inventory
    Route::get('/listings', [DealerPortalController::class, 'listings'])->name('dealer.listings.index');
    Route::get('/listings/create', [DealerPortalController::class, 'createListing'])->name('dealer.listings.create');
    Route::post('/listings', [DealerPortalController::class, 'storeListing'])->name('dealer.listings.store');
    Route::post('/listings/ai-detect', [DealerPortalController::class, 'aiDetectListing'])->name('dealer.listings.ai-detect');

    // Orders
    Route::get('/orders', [DealerPortalController::class, 'orders'])->name('dealer.orders.index');

    // Staff
    Route::get('/staff', [DealerPortalController::class, 'staff'])->name('dealer.staff.index');
    Route::post('/staff', [DealerPortalController::class, 'storeStaff'])->name('dealer.staff.store');

    // Settings
    Route::get('/settings', [DealerPortalController::class, 'settings'])->name('dealer.settings.index');
    Route::post('/settings', [DealerPortalController::class, 'updateSettings'])->name('dealer.settings.update');

    // Subscription & Plans
    Route::get('/subscription', [DealerPortalController::class, 'subscription'])->name('dealer.subscription.index');
    Route::post('/subscription', [DealerPortalController::class, 'subscribe'])->name('dealer.subscription.subscribe');
});

// ==========================================
// SELLER PORTAL
// ==========================================

Route::prefix('seller')->middleware(['auth', 'role:SELLER'])->group(function () {
    Route::get('/', [DealerPortalController::class, 'listings'])->name('seller.dashboard');
    Route::get('/listings/create', [DealerPortalController::class, 'createListing'])->name('seller.listings.create');
    Route::post('/listings', [DealerPortalController::class, 'storeListing'])->name('seller.listings.store');
    Route::post('/listings/ai-detect', [DealerPortalController::class, 'aiDetectListing'])->name('seller.listings.ai-detect');
});

// ==========================================
// CUSTOMER PORTAL
// ==========================================

Route::prefix('customer')->middleware(['auth'])->group(function () {
    Route::get('/', [CustomerPortalController::class, 'dashboard'])->name('customer.dashboard');
    Route::get('/orders', [CustomerPortalController::class, 'orders'])->name('customer.orders.index');
    Route::get('/appointments', [CustomerPortalController::class, 'appointments'])->name('customer.appointments.index');
});

// ==========================================
// UNIVERSAL CONTEXTUAL AI CHAT
// ==========================================

Route::post('/ai/chat', [AiChatController::class, 'chat'])->name('ai.chat');
Route::get('/ai/chat/history', [AiChatController::class, 'history'])->name('ai.chat.history');
Route::post('/ai/chat/clear', [AiChatController::class, 'clear'])->name('ai.chat.clear');

