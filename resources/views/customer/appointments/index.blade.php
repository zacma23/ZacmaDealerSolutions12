@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">My Scheduled Appointments</h1>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-5">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-bold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="px-4 py-3">Appointment Title</th>
                        <th class="px-4 py-3">Merchant / Dealer</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Scheduled Date & Time</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Location</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($appointments as $a)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-4 py-3 font-bold text-slate-900">{{ $a->title }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ $a->organization->name }}</td>
                            <td class="px-4 py-3 uppercase font-bold text-[10px] text-blue-600">{{ $a->type }}</td>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $a->start_time->format('l, M d, Y @ H:i') }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-50 text-blue-700">
                                    {{ $a->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-slate-500">{{ $a->location }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">No scheduled visits.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $appointments->links() }}</div>
    </div>
</div>
@endsection
