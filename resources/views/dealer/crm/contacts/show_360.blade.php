@extends('layouts.admin')

@section('title', 'Customer 360: ' . $contact->full_name)

@section('content')
<div class="space-y-8" x-data="{ dealModal: false, taskModal: false, apptModal: false }">
    <!-- Top Contact Summary Banner -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white font-extrabold text-2xl shadow">
                {{ strtoupper(substr($contact->first_name, 0, 1)) }}
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <h2 class="text-xl font-bold text-slate-900">{{ $contact->full_name }}</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-blue-50 text-blue-700">
                        {{ $contact->contact_type }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                        Status: {{ $contact->status }}
                    </span>
                </div>
                    @if($contact->phone)
                        @if(Auth::user()->organization && !Auth::user()->organization->canAccessCustomerPhone())
                            <span class="inline-flex items-center gap-1.5 bg-amber-50 border border-amber-200 text-amber-900 px-2 py-0.5 rounded-lg text-xs" title="Upgrade to Premium or Advance to unlock full customer numbers and WhatsApp.">
                                <i class="fa-solid fa-lock text-amber-600 text-[10px]"></i>
                                <span>{{ substr($contact->phone, 0, 6) }}••••{{ substr($contact->phone, -2) }}</span>
                                <a href="{{ route('dealer.subscription.index') }}" class="text-blue-600 font-bold hover:underline ml-1">Upgrade to Reveal</a>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5">
                                <i class="fa-solid fa-phone mr-1"></i>
                                <a href="tel:{{ $contact->phone }}" class="hover:text-blue-600 hover:underline font-semibold">{{ $contact->phone }}</a>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $contact->phone) }}" target="_blank" class="px-2 py-0.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[10px] font-bold inline-flex items-center gap-1">
                                    <i class="fa-brands fa-whatsapp"></i> WhatsApp
                                </a>
                            </span>
                        @endif
                    @endif
                    @if($contact->company)<span><i class="fa-solid fa-building mr-1"></i>{{ $contact->company }}</span>@endif
                    <span><i class="fa-solid fa-user mr-1"></i>Agent: {{ $contact->assignedUser?->name ?? 'Unassigned' }}</span>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <button @click="dealModal = true" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow transition">
                + New Deal
            </button>
            <button @click="taskModal = true" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold shadow transition">
                + Add Task
            </button>
        </div>
    </div>

    <!-- AI Intelligence Panel -->
    <div class="bg-gradient-to-r from-indigo-900 to-slate-900 text-white rounded-2xl p-6 shadow-md space-y-4">
        <div class="flex justify-between items-center pb-2 border-b border-white/10">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-sparkles text-amber-300"></i>
                <h3 class="font-bold text-sm tracking-wide uppercase">AI Customer Intelligence (Gemini Assisted)</h3>
            </div>
            <form action="{{ route('dealer.crm.contacts.ai-summary', $contact->id) }}" method="POST">
                @csrf
                <button type="submit" class="bg-white/10 hover:bg-white/20 text-xs font-semibold px-3 py-1.5 rounded-lg transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-rotate text-[10px]"></i>
                    <span>Generate / Refresh Summary</span>
                </button>
            </form>
        </div>

        <div class="text-xs sm:text-sm text-indigo-100 leading-relaxed bg-white/5 p-4 rounded-xl border border-white/10">
            {{ $contact->ai_summary ?: 'Click "Generate / Refresh Summary" to synthesize this customer\'s interactions, inquiries, deals, and recommended follow-up actions.' }}
        </div>

        <!-- AI Message Drafter -->
        <div class="pt-2 flex flex-col sm:flex-row items-center gap-3">
            <form action="{{ route('dealer.crm.contacts.ai-draft', $contact->id) }}" method="POST" class="flex-1 flex gap-2 w-full">
                @csrf
                <select name="channel" class="bg-slate-800 border border-slate-700 text-white text-xs rounded-xl px-3 py-2 outline-none">
                    <option value="sms">Draft SMS</option>
                    <option value="email">Draft Email</option>
                </select>
                <input type="text" name="instruction" placeholder="Optional instruction, e.g., 'offer 5% discount'..." class="flex-1 bg-slate-800 border border-slate-700 text-white text-xs rounded-xl px-3 py-2 outline-none">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold px-4 py-2 rounded-xl transition flex-shrink-0">
                    Draft Message
                </button>
            </form>
        </div>

        @if(session('draft_content'))
            <div class="bg-slate-800/90 border border-indigo-500/40 p-4 rounded-xl space-y-2 mt-3">
                <div class="flex justify-between items-center text-xs text-amber-300 font-bold">
                    <span>Generated {{ strtoupper(session('draft_channel')) }} Draft (Requires Human Approval)</span>
                </div>
                <div class="text-xs text-white whitespace-pre-line font-mono bg-slate-900/60 p-3 rounded-lg border border-slate-700">
                    {{ session('draft_content') }}
                </div>
            </div>
        @endif
    </div>

    <!-- 3-Column Layout: Pipeline Deals, Timeline, Tasks & Appointments -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Deals & Opportunities -->
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                    <h3 class="font-bold text-xs uppercase tracking-wider text-slate-800">Deals & Opportunities</h3>
                    <span class="text-xs font-bold text-blue-600">{{ $contact->deals->count() }} deals</span>
                </div>

                <div class="space-y-3">
                    @forelse($contact->deals as $d)
                        <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/50 space-y-2 text-xs">
                            <div class="flex justify-between items-start">
                                <h4 class="font-bold text-slate-900">{{ $d->title }}</h4>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold text-white" style="background-color: {{ $d->stage->color }};">
                                    {{ $d->stage->name }}
                                </span>
                            </div>
                            <div class="text-slate-500">Value: <strong class="text-slate-900">{{ $d->currency }} {{ number_format($d->value, 2) }}</strong></div>
                        </div>
                    @empty
                        <div class="py-4 text-center text-xs text-slate-400">No deals created yet.</div>
                    @endforelse
                </div>
            </div>

            <!-- Inquiries / Leads -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                <h3 class="font-bold text-xs uppercase tracking-wider text-slate-800 pb-2 border-b border-slate-100">Inquiries</h3>
                <div class="space-y-2 text-xs">
                    @forelse($contact->leads as $l)
                        <div class="p-3 rounded-lg border border-slate-100 bg-slate-50 space-y-1">
                            <div class="font-bold text-slate-800">{{ $l->listing?->title ?? 'General Inquiry' }}</div>
                            <div class="text-slate-400 text-[11px]">{{ $l->created_at->diffForHumans() }} • Score: {{ $l->score }}/100</div>
                        </div>
                    @empty
                        <div class="py-2 text-slate-400 text-xs">No inquiries logged.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Activity Timeline -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
            <h3 class="font-bold text-xs uppercase tracking-wider text-slate-800 pb-2 border-b border-slate-100">
                Customer Timeline
            </h3>

            <div class="space-y-4 text-xs max-h-[500px] overflow-y-auto pr-1">
                @forelse($contact->activities as $act)
                    <div class="flex space-x-3 pb-3 border-b border-slate-50 last:border-0">
                        <div class="w-6 h-6 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-[10px] flex-shrink-0 mt-0.5">
                            <i class="fa-solid fa-circle-dot"></i>
                        </div>
                        <div class="flex-1">
                            <div class="font-bold text-slate-900">{{ $act->subject }}</div>
                            <p class="text-slate-500 text-[11px] mt-0.5">{{ $act->description }}</p>
                            <span class="text-[10px] text-slate-400 block mt-1">{{ $act->occurred_at->format('M d, Y H:i') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-slate-400">No activity logged.</div>
                @endforelse
            </div>
        </div>

        <!-- Tasks & Appointments -->
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                    <h3 class="font-bold text-xs uppercase tracking-wider text-slate-800">Tasks & Follow-ups</h3>
                </div>

                <div class="space-y-2 text-xs">
                    @forelse($contact->tasks as $t)
                        <div class="p-3 rounded-lg border border-slate-200 bg-slate-50 flex items-center justify-between">
                            <div>
                                <div class="font-bold text-slate-900 {{ $t->status === 'completed' ? 'line-through text-slate-400' : '' }}">{{ $t->title }}</div>
                                <div class="text-[10px] text-slate-400">Due: {{ $t->due_date?->format('M d, H:i') }}</div>
                            </div>
                            <form action="{{ route('dealer.crm.tasks.toggle', $t->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-xs {{ $t->status === 'completed' ? 'text-emerald-600' : 'text-slate-400 hover:text-emerald-600' }}">
                                    <i class="fa-solid fa-circle-check text-base"></i>
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="py-4 text-center text-xs text-slate-400">No tasks scheduled.</div>
                    @endforelse
                </div>
            </div>

            <!-- Appointments -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                <h3 class="font-bold text-xs uppercase tracking-wider text-slate-800 pb-2 border-b border-slate-100">Scheduled Appointments</h3>
                <div class="space-y-2 text-xs">
                    @forelse($contact->appointments as $appt)
                        <div class="p-3 rounded-lg border border-slate-100 bg-slate-50">
                            <div class="font-bold text-slate-900">{{ $appt->title }}</div>
                            <div class="text-slate-500 text-[11px]">{{ $appt->start_time->format('M d, Y @ H:i') }} • {{ $appt->status }}</div>
                        </div>
                    @empty
                        <div class="py-2 text-slate-400 text-xs">No appointments scheduled.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Create Deal Modal -->
    <div x-show="dealModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.away="dealModal = false">
            <h3 class="font-bold text-base text-slate-900">Add Opportunity for {{ $contact->full_name }}</h3>
            <form action="{{ route('dealer.crm.deals.store') }}" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="contact_id" value="{{ $contact->id }}">
                <div>
                    <label class="text-xs font-semibold block mb-1">Deal Title</label>
                    <input type="text" name="title" required placeholder="e.g. 2024 Toyota Land Cruiser Purchase" class="w-full text-xs border border-slate-300 rounded-lg p-2.5">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-xs font-semibold block mb-1">Value</label>
                        <input type="number" step="0.01" name="value" required placeholder="1500000" class="w-full text-xs border border-slate-300 rounded-lg p-2.5">
                    </div>
                    <div>
                        <label class="text-xs font-semibold block mb-1">Currency</label>
                        <input type="text" name="currency" value="ETB" class="w-full text-xs border border-slate-300 rounded-lg p-2.5">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-xs font-semibold block mb-1">Pipeline Stage</label>
                        <select name="deal_stage_id" class="w-full text-xs border border-slate-300 rounded-lg p-2.5 bg-white">
                            @foreach($stages as $stg)
                                <option value="{{ $stg->id }}">{{ $stg->name }} ({{ $stg->win_probability }}%)</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold block mb-1">Probability (%)</label>
                        <input type="number" name="probability" value="50" class="w-full text-xs border border-slate-300 rounded-lg p-2.5">
                    </div>
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-lg text-xs shadow mt-2">
                    Create Deal
                </button>
            </form>
        </div>
    </div>

    <!-- Create Task Modal -->
    <div x-show="taskModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.away="taskModal = false">
            <h3 class="font-bold text-base text-slate-900">Add Task for {{ $contact->full_name }}</h3>
            <form action="{{ route('dealer.crm.tasks.store') }}" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="contact_id" value="{{ $contact->id }}">
                <div>
                    <label class="text-xs font-semibold block mb-1">Task Title</label>
                    <input type="text" name="title" required placeholder="e.g. Send financing quote / schedule inspection" class="w-full text-xs border border-slate-300 rounded-lg p-2.5">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-xs font-semibold block mb-1">Priority</label>
                        <select name="priority" class="w-full text-xs border border-slate-300 rounded-lg p-2.5 bg-white">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold block mb-1">Due Date</label>
                        <input type="datetime-local" name="due_date" required class="w-full text-xs border border-slate-300 rounded-lg p-2.5">
                    </div>
                </div>
                <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-2.5 rounded-lg text-xs shadow mt-2">
                    Schedule Task
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
