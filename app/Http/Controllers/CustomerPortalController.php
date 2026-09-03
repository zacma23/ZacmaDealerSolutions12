<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Lead;
use App\Models\Order;
use App\Models\Scopes\TenantScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerPortalController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        $stats = [
            'inquiries_count' => Lead::withoutGlobalScope(TenantScope::class)->whereHas('contact', fn($q) => $q->where('email', $user->email))->count(),
            'appointments_count' => Appointment::withoutGlobalScope(TenantScope::class)->whereHas('contact', fn($q) => $q->where('email', $user->email))->count(),
            'orders_count' => Order::withoutGlobalScope(TenantScope::class)->where('user_id', $user->id)->count(),
        ];

        $myInquiries = Lead::withoutGlobalScope(TenantScope::class)
            ->with(['listing', 'organization'])
            ->whereHas('contact', fn($q) => $q->where('email', $user->email))
            ->latest()
            ->take(5)
            ->get();

        $myAppointments = Appointment::withoutGlobalScope(TenantScope::class)
            ->with(['listing', 'organization'])
            ->whereHas('contact', fn($q) => $q->where('email', $user->email))
            ->orderBy('start_time')
            ->take(5)
            ->get();

        $myOrders = Order::withoutGlobalScope(TenantScope::class)
            ->with(['items.listing', 'payments', 'organization'])
            ->where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        return view('customer.dashboard', compact('user', 'stats', 'myInquiries', 'myAppointments', 'myOrders'));
    }

    public function orders()
    {
        $orders = Order::withoutGlobalScope(TenantScope::class)
            ->with(['items.listing', 'payments', 'organization', 'invoice'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('customer.orders.index', compact('orders'));
    }

    public function appointments()
    {
        $appointments = Appointment::withoutGlobalScope(TenantScope::class)
            ->with(['listing', 'organization'])
            ->whereHas('contact', fn($q) => $q->where('email', Auth::user()->email))
            ->latest('start_time')
            ->paginate(10);

        return view('customer.appointments.index', compact('appointments'));
    }
}
