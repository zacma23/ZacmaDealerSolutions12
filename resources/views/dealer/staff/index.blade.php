@extends('layouts.admin')

@section('title', 'Staff & Team Management')

@section('content')
<div class="space-y-6" x-data="{ staffModal: false }">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Staff & Sales Team</h2>
            <p class="text-xs text-slate-500">Manage agents, managers, and assign CRM permissions</p>
        </div>
        <button @click="staffModal = true" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow transition flex items-center space-x-1.5">
            <i class="fa-solid fa-user-plus"></i>
            <span>Add Team Member</span>
        </button>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-5">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-bold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="px-4 py-3">Member</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3">Phone</th>
                        <th class="px-4 py-3">Assigned Leads</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Joined</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($staff as $u)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900 text-sm">{{ $u->name }}</div>
                                <div class="text-[11px] text-slate-400">{{ $u->email }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-blue-50 text-blue-700">
                                    {{ $u->role }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $u->phone ?? '—' }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ $u->assignedLeads->count() }} leads</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $u->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ $u->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-slate-400 text-[11px]">{{ $u->created_at->format('M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-slate-400">No staff members found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Staff Modal -->
    <div x-show="staffModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.away="staffModal = false">
            <h3 class="font-bold text-base text-slate-900">Add Team Member</h3>
            <form action="{{ route('dealer.staff.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="text-xs font-semibold block mb-1">Full Name</label>
                    <input type="text" name="name" required class="w-full text-xs border border-slate-300 rounded-lg p-2.5">
                </div>
                <div>
                    <label class="text-xs font-semibold block mb-1">Email Address</label>
                    <input type="email" name="email" required class="w-full text-xs border border-slate-300 rounded-lg p-2.5">
                </div>
                <div>
                    <label class="text-xs font-semibold block mb-1">Phone Number</label>
                    <input type="text" name="phone" class="w-full text-xs border border-slate-300 rounded-lg p-2.5">
                </div>
                <div>
                    <label class="text-xs font-semibold block mb-1">Role</label>
                    <select name="role" class="w-full text-xs border border-slate-300 rounded-lg p-2.5 bg-white">
                        <option value="SALES_AGENT" selected>Sales Agent</option>
                        <option value="MANAGER">Sales Manager</option>
                        <option value="STAFF">Support / Staff</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold block mb-1">Temporary Password</label>
                    <input type="password" name="password" required class="w-full text-xs border border-slate-300 rounded-lg p-2.5">
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-lg text-xs shadow mt-2">
                    Create Member Account
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
