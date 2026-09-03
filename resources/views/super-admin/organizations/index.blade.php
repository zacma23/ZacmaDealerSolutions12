@extends('layouts.admin')

@section('title', 'Manage Organizations & Dealerships')

@section('content')
<div class="space-y-6" x-data="{ createModal: false }">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Organizations & Tenants</h2>
            <p class="text-xs text-slate-500">Create, suspend, and configure multi-tenant organizations</p>
        </div>
        <button @click="createModal = true" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow transition flex items-center space-x-1.5">
            <i class="fa-solid fa-plus"></i>
            <span>Add Organization</span>
        </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-bold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="px-6 py-3.5">Organization</th>
                        <th class="px-6 py-3.5">Subdomain</th>
                        <th class="px-6 py-3.5">Plan Tier</th>
                        <th class="px-6 py-3.5">Users</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($organizations as $org)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $org->name }}</div>
                                <div class="text-[11px] text-slate-400">{{ $org->city }}, {{ $org->country }} • Currency: {{ $org->currency }}</div>
                            </td>
                            <td class="px-6 py-4 font-mono text-[11px] text-blue-600">
                                {{ $org->subdomain }}.zacmaa.net
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-slate-100 text-slate-800 px-2 py-0.5 rounded font-semibold text-[11px]">
                                    {{ $org->plan?->name ?? 'Default' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                {{ $org->users->count() }} staff
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase {{ $org->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ $org->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <form action="{{ route('super-admin.organizations.toggle', $org->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 text-[11px] font-semibold rounded-lg border {{ $org->status === 'active' ? 'text-amber-700 bg-amber-50 border-amber-200 hover:bg-amber-100' : 'text-emerald-700 bg-emerald-50 border-emerald-200 hover:bg-emerald-100' }}">
                                        {{ $org->status === 'active' ? 'Suspend' : 'Activate' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400">No organizations created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 border-t border-slate-100">
            {{ $organizations->links() }}
        </div>
    </div>

    <!-- Create Organization Modal -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4" @click.away="createModal = false">
            <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                <h3 class="font-bold text-base text-slate-900">Provision New Organization / Dealer</h3>
                <button @click="createModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form action="{{ route('super-admin.organizations.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="text-xs font-semibold text-slate-700 block mb-1">Organization / Business Name</label>
                    <input type="text" name="name" required placeholder="e.g. Apex Auto Dealership" class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Currency</label>
                        <select name="currency" class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none bg-white">
                            <option value="ETB">ETB (Ethiopian Birr)</option>
                            <option value="USD">USD (US Dollar)</option>
                            <option value="EUR">EUR (Euro)</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Subscription Plan</label>
                        <select name="subscription_plan_id" class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none bg-white">
                            @foreach($plans as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->currency }} {{ $p->price }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100">
                    <span class="text-xs font-bold text-blue-600 uppercase tracking-wider block mb-2">Initial Administrator Credentials</span>
                    <div class="space-y-3">
                        <div>
                            <label class="text-xs font-semibold text-slate-700 block mb-1">Admin Name</label>
                            <input type="text" name="admin_name" required placeholder="Dealership Manager" class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-700 block mb-1">Admin Email (Login ID)</label>
                            <input type="email" name="admin_email" required placeholder="admin@apexauto.com" class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-700 block mb-1">Temporary Password</label>
                            <input type="password" name="admin_password" required placeholder="••••••••" class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none">
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-lg text-xs shadow transition mt-2">
                    Provision Organization & Launch Portal
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
