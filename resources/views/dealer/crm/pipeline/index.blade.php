@extends('layouts.admin')

@section('title', 'CRM Sales Pipeline (Kanban)')

@section('content')
<div class="space-y-6" x-data="{ newDealModal: false }">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Visual Sales Pipeline (Kanban)</h2>
            <p class="text-xs text-slate-500">Track deal progress, probability, and transition opportunities across stages</p>
        </div>
        <button @click="newDealModal = true" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow transition flex items-center space-x-1.5">
            <i class="fa-solid fa-plus"></i>
            <span>Add Pipeline Deal</span>
        </button>
    </div>

    <!-- Kanban Board Container -->
    <div class="flex space-x-4 overflow-x-auto pb-6 pt-2">
        @foreach($stages as $stage)
            <div class="w-80 flex-shrink-0 bg-slate-200/70 rounded-2xl p-4 flex flex-col max-h-[80vh]">
                <!-- Column Header -->
                <div class="flex justify-between items-center pb-3 mb-3 border-b border-slate-300/80">
                    <div class="flex items-center space-x-2">
                        <span class="w-3 h-3 rounded-full" style="background-color: {{ $stage->color }}"></span>
                        <h3 class="font-bold text-xs uppercase tracking-wider text-slate-800">{{ $stage->name }}</h3>
                        <span class="bg-white text-slate-700 text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm">
                            {{ $stage->deals->count() }}
                        </span>
                    </div>
                    <span class="text-[11px] font-bold text-slate-500">
                        {{ $stage->win_probability }}%
                    </span>
                </div>

                <!-- Column Cards Scrollable -->
                <div class="flex-1 overflow-y-auto space-y-3 pr-1">
                    @forelse($stage->deals as $deal)
                        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm hover:shadow-md transition space-y-3 text-xs">
                            <div class="flex justify-between items-start">
                                <h4 class="font-bold text-slate-900 text-sm leading-tight">{{ $deal->title }}</h4>
                                <span class="text-blue-600 font-extrabold">{{ $deal->currency }} {{ number_format($deal->value) }}</span>
                            </div>

                            <div class="text-[11px] text-slate-500 space-y-1">
                                <div class="flex items-center space-x-1.5">
                                    <i class="fa-solid fa-user text-slate-400 text-[10px]"></i>
                                    <a href="{{ route('dealer.crm.contacts.360', $deal->contact_id) }}" class="font-semibold text-slate-700 hover:text-blue-600">
                                        {{ $deal->contact?->full_name }}
                                    </a>
                                </div>
                                @if($deal->listing)
                                    <div class="flex items-center space-x-1.5 text-slate-400 truncate">
                                        <i class="fa-solid fa-box text-[10px]"></i>
                                        <span>{{ $deal->listing->title }}</span>
                                    </div>
                                @endif
                                @if($deal->assignedUser)
                                    <div class="flex items-center space-x-1.5 text-slate-400">
                                        <i class="fa-solid fa-id-badge text-[10px]"></i>
                                        <span>{{ $deal->assignedUser->name }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Stage Transition Selector -->
                            <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                                <form action="{{ route('dealer.crm.deals.move', $deal->id) }}" method="POST" class="w-full">
                                    @csrf
                                    <select name="deal_stage_id" onchange="this.form.submit()" class="w-full text-[11px] border border-slate-200 rounded-lg p-1.5 outline-none bg-slate-50 font-medium text-slate-700">
                                        @foreach($stages as $s)
                                            <option value="{{ $s->id }}" {{ $s->id === $stage->id ? 'selected' : '' }}>
                                                Move &rarr; {{ $s->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-slate-400">
                            No deals in this stage.
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    <!-- Create Deal Modal -->
    <div x-show="newDealModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.away="newDealModal = false">
            <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                <h3 class="font-bold text-base text-slate-900">Add Deal to Sales Pipeline</h3>
                <button @click="newDealModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form action="{{ route('dealer.crm.deals.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="text-xs font-semibold text-slate-700 block mb-1">Deal Title</label>
                    <input type="text" name="title" required placeholder="e.g. Purchase of 2023 Mercedes C200" class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none">
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-700 block mb-1">Associated Contact</label>
                    <select name="contact_id" required class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none bg-white">
                        @foreach($contacts as $c)
                            <option value="{{ $c->id }}">{{ $c->full_name }} ({{ $c->email ?: $c->phone }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Deal Value</label>
                        <input type="number" step="0.01" name="value" required placeholder="500000" class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Currency</label>
                        <select name="currency" class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none bg-white">
                            <option value="ETB">ETB</option>
                            <option value="USD">USD</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Initial Stage</label>
                        <select name="deal_stage_id" class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none bg-white">
                            @foreach($stages as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Probability (%)</label>
                        <input type="number" name="probability" value="25" min="0" max="100" class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none">
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-lg text-xs shadow transition mt-2">
                    Add Deal to Pipeline
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
