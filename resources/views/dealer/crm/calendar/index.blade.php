@extends('layouts.admin')

@section('title', 'Calendar & Appointments')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Calendar & Appointments</h2>
        <p class="text-xs text-slate-500">Scheduled vehicle test drives, property viewings, and customer meetings</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Appointments -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <h3 class="font-bold text-sm text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center space-x-2">
                <i class="fa-solid fa-calendar-check text-blue-600"></i>
                <span>Upcoming Appointments ({{ $appointments->count() }})</span>
            </h3>

            <div class="space-y-3 text-xs">
                @forelse($appointments as $appt)
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 space-y-2">
                        <div class="flex justify-between items-start">
                            <h4 class="font-bold text-slate-900 text-sm">{{ $appt->title }}</h4>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-100 text-blue-800">
                                {{ $appt->type }}
                            </span>
                        </div>
                        <div class="text-slate-600">
                            Client: <strong>{{ $appt->contact?->full_name }}</strong> ({{ $appt->contact?->phone }})
                        </div>
                        <div class="text-slate-400 text-[11px] flex items-center space-x-2">
                            <i class="fa-solid fa-clock"></i>
                            <span>{{ $appt->start_time->format('l, F j, Y @ g:i A') }}</span>
                            <span>•</span>
                            <span>{{ $appt->location }}</span>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-400 text-xs">No upcoming appointments scheduled.</div>
                @endforelse
            </div>
        </div>

        <!-- Scheduled Tasks Schedule -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <h3 class="font-bold text-sm text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center space-x-2">
                <i class="fa-solid fa-list-check text-indigo-600"></i>
                <span>Scheduled Action Deadlines</span>
            </h3>

            <div class="space-y-3 text-xs">
                @forelse($tasks as $t)
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 flex justify-between items-center">
                        <div>
                            <div class="font-bold text-slate-900">{{ $t->title }}</div>
                            <div class="text-slate-500 text-[11px]">Client: {{ $t->contact?->full_name ?? 'General' }}</div>
                        </div>
                        <span class="text-xs font-semibold text-slate-700 bg-white px-3 py-1 rounded-lg border border-slate-200">
                            {{ $t->due_date?->format('M d, H:i') }}
                        </span>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-400 text-xs">No scheduled task deadlines.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
