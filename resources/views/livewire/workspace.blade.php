@php
    $titles = [
        'overview' => ['Good to see you', 'Here’s what is happening across your workspace.'],
        'customers' => ['Customers', 'Keep client details and activity in one place.'],
        'projects' => ['Projects', 'Track every active engagement from kickoff to delivery.'],
        'tickets' => ['Tickets', 'Triage requests and keep client work moving.'],
        'tasks' => ['Tasks', 'A focused view of the work that needs doing.'],
        'time' => ['Time entries', 'A clear record of where your time went.'],
    ];
    [$pageTitle, $pageDescription] = $titles[$section] ?? $titles['overview'];
@endphp

<div class="min-h-screen" wire:poll.15s>
    <header class="sticky top-0 z-10 border-b border-zinc-200/70 bg-zinc-50/85 backdrop-blur-xl dark:border-white/10 dark:bg-zinc-950/85">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-4 sm:px-8">
            <div class="min-w-0"><p class="eyebrow">Falqo workspace</p><h1 class="truncate text-xl font-semibold tracking-tight sm:text-2xl">{{ $pageTitle }}</h1></div>
            <div class="flex items-center gap-2">
                @if($activeTimer)
                    <div class="hidden items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-800 sm:flex dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-300"><span class="status-dot"></span><span class="max-w-40 truncate">{{ $activeTimer->project->name }}</span></div>
                    <flux:button wire:click="stopTimer" variant="danger" icon="stop">Stop timer</flux:button>
                @else
                    <flux:modal.trigger name="start-timer"><flux:button variant="primary" icon="play">Start timer</flux:button></flux:modal.trigger>
                @endif
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl space-y-7 px-5 py-7 sm:px-8 sm:py-9">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div><h2 class="text-2xl font-semibold tracking-tight sm:text-3xl">{{ $pageTitle }}</h2><p class="mt-1 max-w-2xl text-sm text-zinc-500 dark:text-zinc-400">{{ $pageDescription }}</p></div>
            @if($section !== 'overview' && $section !== 'time')
                <flux:modal.trigger name="create-{{ rtrim($section, 's') }}"><flux:button variant="primary" icon="plus">New {{ rtrim($section, 's') }}</flux:button></flux:modal.trigger>
            @endif
        </div>

        @if($errors->any())<flux:callout variant="danger" icon="exclamation-triangle" heading="Something needs your attention">{{ $errors->first() }}</flux:callout>@endif

        @if($section === 'overview')
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach([
                    ['Customers',$customers->count(),'users','from your client list'],
                    ['Active projects',$projects->where('status','active')->count(),'folder','currently in progress'],
                    ['Open tickets',$tickets->where('status','open')->count(),'ticket','waiting for attention'],
                    ['Tracked today',intdiv($minutesToday,60).'h '.($minutesToday%60).'m','clock','logged so far today']
                ] as [$label,$value,$icon,$hint])
                    <article class="stat-card"><div class="flex items-start justify-between"><p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ $label }}</p><span class="rounded-xl bg-falqo-50 p-2 text-falqo-700 dark:bg-falqo-950 dark:text-falqo-300"><flux:icon :name="$icon" class="size-5" /></span></div><p class="mt-4 text-3xl font-semibold tracking-tight">{{ $value }}</p><p class="mt-1 text-xs text-zinc-400">{{ $hint }}</p></article>
                @endforeach
            </section>

            <section class="grid gap-5 lg:grid-cols-[1.25fr_.75fr]">
                <article class="surface p-5 sm:p-6">
                    <div class="flex items-center justify-between"><div><h3 class="font-semibold">Recent activity</h3><p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Your latest tracked sessions</p></div><flux:button :href="route('dashboard',['section'=>'time'])" wire:navigate variant="ghost" size="sm">View all</flux:button></div>
                    <div class="mt-4">@forelse($entries->take(5) as $entry)<div class="item"><div class="flex min-w-0 items-center gap-3"><span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 font-semibold text-zinc-600 dark:bg-white/10 dark:text-zinc-300">{{ strtoupper(substr($entry->project->name,0,1)) }}</span><div class="min-w-0"><p class="truncate text-sm font-medium">{{ $entry->project->name }}</p><p class="truncate text-xs text-zinc-500">{{ $entry->description ?: 'General project work' }} · {{ $entry->started_at->diffForHumans() }}</p></div></div><span class="shrink-0 font-mono text-sm tabular-nums">{{ intdiv($entry->minutes,60) }}:{{ str_pad($entry->minutes%60,2,'0',STR_PAD_LEFT) }}</span></div>@empty <x-empty icon="clock" /> @endforelse</div>
                </article>
                <article class="surface overflow-hidden p-5 sm:p-6"><span class="inline-flex rounded-xl bg-falqo-100 p-3 text-falqo-700 dark:bg-falqo-950 dark:text-falqo-300"><flux:icon name="bolt" class="size-6" /></span><h3 class="mt-5 text-xl font-semibold">Ready to focus?</h3><p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">Pick a project, describe what you’re doing, and let Falqo keep track.</p>@if($activeTimer)<flux:button wire:click="stopTimer" variant="danger" class="mt-6 w-full">Stop current timer</flux:button>@else<flux:modal.trigger name="start-timer"><flux:button variant="primary" icon="play" class="mt-6 w-full">Start tracking</flux:button></flux:modal.trigger>@endif</article>
            </section>
        @elseif($section === 'customers')
            <section class="surface px-5 sm:px-6">@forelse($customers as $customer)<div class="item"><div class="flex min-w-0 items-center gap-3"><span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-falqo-100 text-sm font-semibold text-falqo-700 dark:bg-falqo-950 dark:text-falqo-300">{{ strtoupper(substr($customer->name,0,2)) }}</span><div class="min-w-0"><p class="truncate font-medium">{{ $customer->name }}</p><small class="block truncate">{{ $customer->email ?: 'No email added' }}</small></div></div><flux:badge size="sm">{{ $customer->projects_count }} {{ Str::plural('project',$customer->projects_count) }}</flux:badge></div>@empty<x-empty icon="users" />@endforelse</section>
        @elseif($section === 'projects')
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">@forelse($projects as $project)<article class="surface p-5 transition hover:-translate-y-0.5 hover:shadow-md"><div class="flex items-start justify-between gap-3"><span class="rounded-xl bg-falqo-50 p-2.5 text-falqo-700 dark:bg-falqo-950 dark:text-falqo-300"><flux:icon name="folder" class="size-5" /></span><flux:badge color="green" size="sm">{{ ucfirst($project->status) }}</flux:badge></div><h3 class="mt-4 font-semibold">{{ $project->name }}</h3><p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $project->customer->name }}</p><p class="mt-4 line-clamp-2 min-h-10 text-sm text-zinc-500 dark:text-zinc-400">{{ $project->description ?: 'No project description yet.' }}</p><div class="mt-5 flex gap-4 border-t border-zinc-100 pt-4 text-xs text-zinc-500 dark:border-white/10"><span>{{ $project->tasks_count }} tasks</span><span>{{ $project->tickets_count }} tickets</span></div></article>@empty<x-empty icon="folder" />@endforelse</section>
        @elseif($section === 'tickets')
            <section class="surface px-5 sm:px-6">@forelse($tickets as $ticket)<div class="item"><div class="min-w-0"><div class="flex items-center gap-2"><span class="text-xs font-medium text-zinc-400">#{{ $ticket->id }}</span><p class="truncate font-medium">{{ $ticket->title }}</p></div><small class="mt-1 block truncate">{{ $ticket->customer->name }}{{ $ticket->project ? ' · '.$ticket->project->name : '' }}</small></div><flux:badge :color="$ticket->priority === 'urgent' ? 'red' : ($ticket->priority === 'high' ? 'amber' : 'zinc')" size="sm">{{ ucfirst($ticket->priority) }}</flux:badge></div>@empty<x-empty icon="ticket" />@endforelse</section>
        @elseif($section === 'tasks')
            <section class="surface px-5 sm:px-6">@forelse($tasks as $task)<button wire:click="toggleTask({{ $task->id }})" class="item w-full rounded-lg text-left focus:outline-none focus:ring-2 focus:ring-falqo-500"><div class="flex min-w-0 items-center gap-3"><span @class(['flex size-6 shrink-0 items-center justify-center rounded-full border-2','border-emerald-500 bg-emerald-500 text-white'=>$task->status==='done','border-zinc-300 dark:border-zinc-600'=>$task->status!=='done'])>@if($task->status==='done')<flux:icon name="check" class="size-3.5" />@endif</span><div class="min-w-0"><p @class(['truncate font-medium','line-through opacity-50'=>$task->status==='done'])>{{ $task->title }}</p><small class="block truncate">{{ $task->project->name }}</small></div></div><flux:badge size="sm">{{ $task->status === 'done' ? 'Done' : 'To do' }}</flux:badge></button>@empty<x-empty icon="check-circle" />@endforelse</section>
        @else
            <section class="surface px-5 sm:px-6">@forelse($entries as $entry)<div class="item"><div class="min-w-0"><p class="truncate font-medium">{{ $entry->project->name }}</p><small class="mt-1 block truncate">{{ $entry->started_at->format('M j, Y · H:i') }}{{ $entry->description ? ' · '.$entry->description : '' }}</small></div><span class="shrink-0 font-mono text-sm tabular-nums">{{ intdiv($entry->minutes,60) }}h {{ $entry->minutes%60 }}m</span></div>@empty<x-empty icon="clock" />@endforelse</section>
        @endif
    </main>

    <flux:modal name="start-timer" class="max-w-lg"><form wire:submit="startTimer" class="space-y-6"><div><flux:heading size="lg">Start a timer</flux:heading><flux:subheading>Choose what you’re working on. You can stop it anytime.</flux:subheading></div><flux:select wire:model.live="projectId" label="Project" placeholder="Choose a project">@foreach($projects as $project)<flux:select.option value="{{ $project->id }}">{{ $project->name }}</flux:select.option>@endforeach</flux:select><flux:select wire:model="taskId" label="Task (optional)" placeholder="General project work">@foreach($tasks->where('project_id',$projectId) as $task)<flux:select.option value="{{ $task->id }}">{{ $task->title }}</flux:select.option>@endforeach</flux:select><flux:input wire:model="description" label="What are you working on?" placeholder="e.g. Homepage revisions" /><div class="flex justify-end gap-2"><flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close><flux:button type="submit" variant="primary" icon="play">Start timer</flux:button></div></form></flux:modal>

    <flux:modal name="create-customer" class="max-w-lg"><form wire:submit="addCustomer" class="space-y-6"><div><flux:heading size="lg">Add a customer</flux:heading><flux:subheading>Create a client profile you can connect to projects.</flux:subheading></div><flux:input wire:model="name" label="Customer name" placeholder="Acme Studio" autofocus /><flux:input wire:model="email" type="email" label="Email (optional)" placeholder="hello@example.com" /><x-modal-actions label="Add customer" /></form></flux:modal>
    <flux:modal name="create-project" class="max-w-lg"><form wire:submit="addProject" class="space-y-6"><div><flux:heading size="lg">Create a project</flux:heading><flux:subheading>Organize tasks, tickets, and tracked time in one place.</flux:subheading></div><flux:input wire:model="name" label="Project name" placeholder="Website redesign" autofocus /><flux:select wire:model="customerId" label="Customer" placeholder="Choose a customer">@foreach($customers as $customer)<flux:select.option value="{{ $customer->id }}">{{ $customer->name }}</flux:select.option>@endforeach</flux:select><flux:textarea wire:model="description" label="Description (optional)" rows="3" /><x-modal-actions label="Create project" /></form></flux:modal>
    <flux:modal name="create-ticket" class="max-w-lg"><form wire:submit="addTicket" class="space-y-6"><div><flux:heading size="lg">Create a ticket</flux:heading><flux:subheading>Capture and prioritize a new customer request.</flux:subheading></div><flux:input wire:model="title" label="Subject" placeholder="What needs attention?" autofocus /><flux:select wire:model="customerId" label="Customer" placeholder="Choose a customer">@foreach($customers as $customer)<flux:select.option value="{{ $customer->id }}">{{ $customer->name }}</flux:select.option>@endforeach</flux:select><flux:select wire:model="projectId" label="Project (optional)" placeholder="No project">@foreach($projects as $project)<flux:select.option value="{{ $project->id }}">{{ $project->name }}</flux:select.option>@endforeach</flux:select><flux:select wire:model="priority" label="Priority"><flux:select.option value="normal">Normal</flux:select.option><flux:select.option value="high">High</flux:select.option><flux:select.option value="urgent">Urgent</flux:select.option></flux:select><x-modal-actions label="Create ticket" /></form></flux:modal>
    <flux:modal name="create-task" class="max-w-lg"><form wire:submit="addTask" class="space-y-6"><div><flux:heading size="lg">Add a task</flux:heading><flux:subheading>Create a clear, actionable next step.</flux:subheading></div><flux:input wire:model="title" label="Task" placeholder="What needs to be done?" autofocus /><flux:select wire:model="projectId" label="Project" placeholder="Choose a project">@foreach($projects as $project)<flux:select.option value="{{ $project->id }}">{{ $project->name }}</flux:select.option>@endforeach</flux:select><x-modal-actions label="Add task" /></form></flux:modal>
</div>
