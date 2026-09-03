@extends('layouts.admin')

@section('title', 'CRM Leads & AI Scoring')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Leads & Intent Intelligence</h2>
            <p class="text-xs text-slate-500">Prioritize incoming inquiries using algorithmic and AI lead scoring</p>
        </div>
    </div>

    <!-- Leads Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-5 space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-bold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="px-4 py-3">Lead / Contact</th>
                        <th class="px-4 py-3">Listing of Interest</th>
                        <th class="px-4 py-3">AI Score</th>
                        <th class="px-4 py-3">Priority</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Assigned Sales Agent</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($leads as $lead)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900 text-sm">
                                    <a href="{{ route('dealer.crm.contacts.360', $lead->contact_id) }}" class="hover:text-blue-600">
                                        {{ $lead->contact?->full_name }}
                                    </a>
                                </div>
                                <div class="text-[11px] text-slate-400">{{ $lead->contact?->email }} • {{ $lead->contact?->phone }}</div>
                            </td>
                            <td class="px-4 py-3 font-semibold text-slate-800 truncate max-w-[160px]">
                                {{ $lead->listing?->title ?? 'General Inquiry' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase inline-flex items-center space-x-1 {{ $lead->score_category === 'hot' ? 'bg-rose-50 text-rose-700' : ($lead->score_category === 'warm' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                                    @if($lead->score_category === 'hot')<i class="fa-solid fa-fire text-rose-500"></i>@endif
                                    <span>{{ $lead->score }}/100 {{ $lead->score_category }}</span>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $lead->priority === 'urgent' ? 'bg-rose-100 text-rose-800' : ($lead->priority === 'high' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">
                                    {{ $lead->priority }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <form action="{{ route('dealer.crm.leads.status', $lead->id) }}" method="POST">
                                    @csrf
                                    <select name="status" onchange="this.form.submit()" class="text-[11px] border border-slate-200 rounded px-2 py-1 outline-none bg-white font-medium">
                                        <option value="new" {{ $lead->status === 'new' ? 'selected' : '' }}>New</option>
                                        <option value="contacted" {{ $lead->status === 'contacted' ? 'selected' : '' }}>Contacted</option>
                                        <option value="qualified" {{ $lead->status === 'qualified' ? 'selected' : '' }}>Qualified</option>
                                        <option value="proposal" {{ $lead->status === 'proposal' ? 'selected' : '' }}>Proposal</option>
                                        <option value="negotiation" {{ $lead->status === 'negotiation' ? 'selected' : '' }}>Negotiation</option>
                                        <option value="won" {{ $lead->status === 'won' ? 'selected' : '' }}>Won</option>
                                        <option value="lost" {{ $lead->status === 'lost' ? 'selected' : '' }}>Lost</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-4 py-3 text-slate-700">{{ $lead->assignedUser?->name ?? 'Unassigned' }}</td>
                            <td class="px-4 py-3 text-right space-x-1.5">
                                <form action="{{ route('dealer.crm.leads.rescore', $lead->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded text-[11px] font-semibold transition" title="Recalculate AI Score">
                                        <i class="fa-solid fa-sparkles mr-0.5"></i> Rescore
                                    </button>
                                </form>
                                <a href="{{ route('dealer.crm.contacts.360', $lead->contact_id) }}" class="px-2.5 py-1 bg-slate-100 text-slate-700 hover:bg-blue-600 hover:text-white rounded text-[11px] font-semibold transition">
                                    360 &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-6 text-center text-slate-400">No leads found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $leads->links() }}</div>
    </div>
</div>
@endsection
