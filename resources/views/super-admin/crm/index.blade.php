@extends('layouts.admin')

@section('title', 'Global Platform CRM')

@section('content')
<div class="space-y-8">
    <div>
        <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Global Platform CRM Overview</h2>
        <p class="text-xs text-slate-500">Cross-tenant aggregation of all CRM leads, contacts, and opportunities</p>
    </div>

    <!-- Leads Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden space-y-4 p-5">
        <h3 class="font-bold text-sm text-slate-900 uppercase tracking-wider">All Platform Leads</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-bold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="px-4 py-3">Lead / Contact</th>
                        <th class="px-4 py-3">Organization</th>
                        <th class="px-4 py-3">Listing</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">AI Score</th>
                        <th class="px-4 py-3">Value</th>
                        <th class="px-4 py-3 text-right">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($leads as $lead)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900">{{ $lead->contact?->full_name ?? 'Inquiry' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $lead->contact?->email }} • {{ $lead->contact?->phone }}</div>
                            </td>
                            <td class="px-4 py-3 font-semibold text-slate-700">{{ $lead->organization?->name }}</td>
                            <td class="px-4 py-3 truncate max-w-[150px]">{{ $lead->listing?->title ?? 'General' }}</td>
                            <td class="px-4 py-3 capitalize font-semibold">{{ $lead->status }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $lead->score_category === 'hot' ? 'bg-rose-50 text-rose-700' : ($lead->score_category === 'warm' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                                    {{ $lead->score }}/100 {{ $lead->score_category }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-bold text-slate-900">{{ $lead->currency }} {{ number_format($lead->estimated_value, 2) }}</td>
                            <td class="px-4 py-3 text-right text-slate-400 text-[11px]">{{ $lead->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-6 text-center text-slate-400">No leads found across organizations.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $leads->links() }}</div>
    </div>
</div>
@endsection
