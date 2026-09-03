@extends('layouts.admin')

@section('title', 'CRM Contacts')

@section('content')
<div class="space-y-6" x-data="{ contactModal: false }">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Customer & Contact Directory</h2>
            <p class="text-xs text-slate-500">Manage leads, buyers, partners, and customer profiles</p>
        </div>
        <button @click="contactModal = true" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow transition flex items-center space-x-1.5">
            <i class="fa-solid fa-plus"></i>
            <span>Add Contact</span>
        </button>
    </div>

    <!-- Contacts Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-5 space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-bold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="px-4 py-3">Contact</th>
                        <th class="px-4 py-3">Company</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Assigned Agent</th>
                        <th class="px-4 py-3">Active Deals</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($contacts as $c)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900 text-sm">
                                    <a href="{{ route('dealer.crm.contacts.360', $c->id) }}" class="hover:text-blue-600">
                                        {{ $c->full_name }}
                                    </a>
                                <div class="text-[11px] text-slate-400 flex items-center gap-1.5 mt-0.5">
                                    <span>{{ $c->email }}</span>
                                    <span>•</span>
                                    @if(Auth::user()->organization && !Auth::user()->organization->canAccessCustomerPhone())
                                        <span class="inline-flex items-center gap-1 text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded text-[10px]" title="Masked on Basic Plan">
                                            <i class="fa-solid fa-lock text-[9px] text-amber-500"></i>
                                            {{ substr($c->phone, 0, 6) }}••••{{ substr($c->phone, -2) }}
                                            <a href="{{ route('dealer.subscription.index') }}" class="text-blue-600 font-bold hover:underline ml-0.5">Unlock</a>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-slate-700 font-medium">
                                            <a href="tel:{{ $c->phone }}" class="hover:text-blue-600 hover:underline">{{ $c->phone }}</a>
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $c->phone) }}" target="_blank" class="text-emerald-600 hover:text-emerald-700 ml-0.5" title="WhatsApp">
                                                <i class="fa-brands fa-whatsapp text-xs"></i>
                                            </a>
                                        </span>
                                    @endif
                                </div>
                            <td class="px-4 py-3 text-slate-700">{{ $c->company ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded text-[10px] font-bold uppercase">
                                    {{ $c->contact_type }}
                                </span>
                            </td>
                            <td class="px-4 py-3 capitalize font-semibold">{{ $c->status }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $c->assignedUser?->name ?? 'Unassigned' }}</td>
                            <td class="px-4 py-3 font-bold text-blue-600">{{ $c->deals->count() }} deals</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('dealer.crm.contacts.360', $c->id) }}" class="px-3 py-1.5 bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white rounded-lg text-xs font-semibold transition">
                                    Customer 360 &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-6 text-center text-slate-400">No contacts found. Click "Add Contact" to create one.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $contacts->links() }}</div>
    </div>

    <!-- Create Contact Modal -->
    <div x-show="contactModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.away="contactModal = false">
            <h3 class="font-bold text-base text-slate-900">Add New Contact</h3>
            <form action="{{ route('dealer.crm.contacts.store') }}" method="POST" class="space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold block mb-1">First Name</label>
                        <input type="text" name="first_name" required class="w-full text-xs border border-slate-300 rounded-lg p-2.5">
                    </div>
                    <div>
                        <label class="text-xs font-semibold block mb-1">Last Name</label>
                        <input type="text" name="last_name" class="w-full text-xs border border-slate-300 rounded-lg p-2.5">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-semibold block mb-1">Email</label>
                    <input type="email" name="email" class="w-full text-xs border border-slate-300 rounded-lg p-2.5">
                </div>
                <div>
                    <label class="text-xs font-semibold block mb-1">Phone</label>
                    <input type="text" name="phone" class="w-full text-xs border border-slate-300 rounded-lg p-2.5">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold block mb-1">Contact Type</label>
                        <select name="contact_type" class="w-full text-xs border border-slate-300 rounded-lg p-2.5 bg-white">
                            <option value="lead">Lead</option>
                            <option value="customer" selected>Customer</option>
                            <option value="partner">Partner</option>
                            <option value="vendor">Vendor</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold block mb-1">Status</label>
                        <select name="status" class="w-full text-xs border border-slate-300 rounded-lg p-2.5 bg-white">
                            <option value="prospect">Prospect</option>
                            <option value="lead">Lead</option>
                            <option value="active">Active</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-lg text-xs shadow mt-2">
                    Save Contact
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
