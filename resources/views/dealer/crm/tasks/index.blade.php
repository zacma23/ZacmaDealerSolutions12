@extends('layouts.admin')

@section('title', 'Tasks & Follow-up Queue')

@section('content')
<div class="space-y-6" x-data="{ taskModal: false }">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Tasks & Follow-up Queue</h2>
            <p class="text-xs text-slate-500">Ensure no customer inquiry or critical milestone is missed</p>
        </div>
        <button @click="taskModal = true" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow transition flex items-center space-x-1.5">
            <i class="fa-solid fa-plus"></i>
            <span>Create Task</span>
        </button>
    </div>

    <!-- Filter Tabs -->
    <div class="flex space-x-2 border-b border-slate-200 pb-2">
        <a href="{{ route('dealer.crm.tasks.index') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold {{ $filter === 'all' ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
            All Tasks
        </a>
        <a href="{{ route('dealer.crm.tasks.index', ['filter' => 'today']) }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold {{ $filter === 'today' ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
            Due Today
        </a>
        <a href="{{ route('dealer.crm.tasks.index', ['filter' => 'overdue']) }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold {{ $filter === 'overdue' ? 'bg-rose-600 text-white' : 'bg-white text-rose-600 border border-rose-200 hover:bg-rose-50' }}">
            Overdue
        </a>
        <a href="{{ route('dealer.crm.tasks.index', ['filter' => 'upcoming']) }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold {{ $filter === 'upcoming' ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
            Upcoming
        </a>
    </div>

    <!-- Tasks List -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-5 space-y-4">
        <div class="divide-y divide-slate-100 text-xs">
            @forelse($tasks as $t)
                <div class="py-3 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <form action="{{ route('dealer.crm.tasks.toggle', $t->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="text-base {{ $t->status === 'completed' ? 'text-emerald-600' : 'text-slate-300 hover:text-emerald-500' }}">
                                <i class="fa-solid fa-circle-check"></i>
                            </button>
                        </form>
                        <div>
                            <div class="font-bold text-slate-900 text-sm {{ $t->status === 'completed' ? 'line-through text-slate-400' : '' }}">
                                {{ $t->title }}
                            </div>
                            <div class="text-[11px] text-slate-400">
                                Contact: <strong>{{ $t->contact?->full_name ?? 'None' }}</strong> • Agent: {{ $t->assignedUser?->name ?? 'Unassigned' }}
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $t->priority === 'urgent' ? 'bg-rose-100 text-rose-800' : ($t->priority === 'high' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">
                            {{ $t->priority }}
                        </span>
                        <span class="text-[11px] font-medium {{ $t->isOverdue() ? 'text-rose-600 font-bold' : 'text-slate-500' }}">
                            {{ $t->due_date?->format('M d, H:i') }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-slate-400">No tasks in this queue.</div>
            @endforelse
        </div>
        <div>{{ $tasks->links() }}</div>
    </div>

    <!-- Create Task Modal -->
    <div x-show="taskModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.away="taskModal = false">
            <h3 class="font-bold text-base text-slate-900">Create Follow-up Task</h3>
            <form action="{{ route('dealer.crm.tasks.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="text-xs font-semibold block mb-1">Task Title</label>
                    <input type="text" name="title" required placeholder="e.g. Call customer to verify financing" class="w-full text-xs border border-slate-300 rounded-lg p-2.5">
                </div>
                <div>
                    <label class="text-xs font-semibold block mb-1">Associated Contact</label>
                    <select name="contact_id" class="w-full text-xs border border-slate-300 rounded-lg p-2.5 bg-white">
                        <option value="">None (Internal task)</option>
                        @foreach($contacts as $c)
                            <option value="{{ $c->id }}">{{ $c->full_name }}</option>
                        @endforeach
                    </select>
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
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-lg text-xs shadow mt-2">
                    Schedule Task
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
