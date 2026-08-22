<?php

namespace App\Livewire;

use App\Enums\ProjectCategory;
use App\Enums\TaskStatus;
use App\Enums\TicketCategory;
use App\Models\CalendarEvent;
use App\Models\Customer;
use App\Models\Project;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\TimeEntry;
use App\Models\User;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class Workspace extends Component
{
    public string $section = 'overview';

    public ?int $showId = null;

    #[Url]
    public string $ticketOrder = 'priority';

    #[Url]
    public bool $groupTickets = false;

    #[Url]
    public string $timeGroup = 'none';

    #[Url]
    public bool $showCompletedTasks = false;

    public string $name = '';

    public string $email = '';

    public string $title = '';

    public string $description = '';

    public string $priority = 'normal';

    public string $category = 'development';

    public int $estimatedHours = 0;

    public int $estimatedMinutes = 0;

    public string $projectIcon = 'folder';

    public string $projectCategory = 'development';

    public string $status = 'todo';

    public ?int $customerId = null;

    public ?int $projectId = null;

    public ?int $taskId = null;

    public ?int $ticketId = null;

    public ?int $timeEntryId = null;

    public ?int $eventId = null;

    public ?int $assignedUserId = null;

    public bool $timeBookmarked = true;

    public string $calendarDate = '';

    public string $weekStart = '';

    public string $selectedDay = '';

    public string $entryDate = '';

    public int $entryHours = 0;

    public int $entryMinutes = 0;

    public string $startsAt = '';

    public string $endsAt = '';

    public bool $isPublic = false;

    public array $attendeeIds = [];

    public function mount(): void
    {
        $this->calendarDate = today()->toDateString();
        $this->weekStart = now()->startOfWeek()->toDateString();
        $this->selectedDay = today()->toDateString();
        $this->assignedUserId = auth()->id();
    }

    private function customers(): Builder
    {
        return Customer::query()->where('user_id', auth()->id());
    }

    private function projects(): Builder
    {
        return Project::query()->whereHas('customer', fn (Builder $q) => $q->where('user_id', auth()->id()));
    }

    private function tickets(): Builder
    {
        return Ticket::query()->whereHas('customer', fn (Builder $q) => $q->where('user_id', auth()->id()));
    }

    private function tasks(): Builder
    {
        return Task::query()->where('assigned_user_id', auth()->id())->whereHas('project.customer', fn (Builder $q) => $q->where('user_id', auth()->id()));
    }

    private function workspaceTasks(): Builder
    {
        return Task::query()->whereHas('project.customer', fn (Builder $q) => $q->where('user_id', auth()->id()));
    }

    private function users(): Builder
    {
        return User::query()->where('company_id', auth()->user()->company_id)->orderBy('name');
    }

    private function calendarEvents(): Builder
    {
        return CalendarEvent::query()->where('company_id', auth()->user()->company_id)->where(function (Builder $query) {
            $query->where('created_by', auth()->id())->orWhere('is_public', true)->orWhereHas('attendees', fn (Builder $attendees) => $attendees->whereKey(auth()->id()));
        });
    }

    public function show(string $type, int $id): void
    {
        abort_unless(in_array($type, ['customer', 'project', 'ticket'], true), 404);
        $query = match ($type) {
            'customer' => $this->customers(), 'project' => $this->projects(), 'ticket' => $this->tickets()
        };
        abort_unless($query->whereKey($id)->exists(), 403);
        $this->redirectRoute(match ($type) {
            'customer' => 'customers.show', 'project' => 'projects.show', 'ticket' => 'tickets.show'
        }, ['showId' => $id], navigate: true);
    }

    public function backTo(string $section): void
    {
        $this->redirectRoute(match ($section) {
            'customers' => 'customers.index', 'projects' => 'projects.index', 'tickets' => 'tickets.index', default => 'dashboard'
        }, navigate: true);
    }

    public function addCustomer(): void
    {
        $data = $this->validate(['name' => 'required|max:120', 'email' => 'nullable|email']);
        Customer::create([...$data, 'user_id' => auth()->id()]);
        $this->reset('name', 'email');
        Flux::modal('create-customer')->close();
        Flux::toast('Customer added', variant: 'success');
    }

    public function addProject(): void
    {
        $this->validate(['name' => 'required|max:160', 'customerId' => 'required|integer', 'description' => 'nullable|max:2000', 'projectCategory' => 'required|in:'.implode(',', array_column(ProjectCategory::cases(), 'value')), 'projectIcon' => 'required|in:folder,globe-alt,shopping-bag,rocket-launch,paint-brush,code-bracket,megaphone,chart-bar,building-office,briefcase,light-bulb,wrench-screwdriver']);
        abort_unless($this->customers()->whereKey($this->customerId)->exists(), 403);
        Project::create(['name' => $this->name, 'icon' => $this->projectIcon, 'category' => $this->projectCategory, 'customer_id' => $this->customerId, 'description' => $this->description]);
        $this->reset('name', 'customerId', 'description', 'projectCategory');
        $this->projectIcon = 'folder';
        Flux::modal('create-project')->close();
        Flux::toast('Project created', variant: 'success');
    }

    public function openProjectIcon(int $projectId): void
    {
        $project = $this->projects()->findOrFail($projectId);
        $this->projectId = $project->id;
        $this->projectIcon = $project->icon;
        Flux::modal('project-icon')->show();
    }

    public function saveProjectIcon(): void
    {
        $this->validate(['projectIcon' => 'required|in:folder,globe-alt,shopping-bag,rocket-launch,paint-brush,code-bracket,megaphone,chart-bar,building-office,briefcase,light-bulb,wrench-screwdriver']);
        $this->projects()->findOrFail($this->projectId)->update(['icon' => $this->projectIcon]);
        Flux::modal('project-icon')->close();
        Flux::toast('Project icon updated', variant: 'success');
    }

    public function prepareTask(?int $projectId = null, ?string $date = null): void
    {
        $this->reset('taskId', 'title', 'description');
        $this->status = TaskStatus::Todo->value;
        $this->projectId = $projectId;
        $this->entryDate = $date ?? '';
        $this->assignedUserId = auth()->id();
        Flux::modal('task-editor')->show();
    }

    public function editTask(int $id): void
    {
        $task = $this->workspaceTasks()->findOrFail($id);
        $this->taskId = $task->id;
        $this->title = $task->title;
        $this->description = $task->description ?? '';
        $this->projectId = $task->project_id;
        $this->entryDate = $task->due_date?->toDateString() ?? '';
        $this->assignedUserId = $task->assigned_user_id;
        $this->status = $task->status->value;
        Flux::modal('task-editor')->show();
    }

    public function saveTask(): void
    {
        $this->validate(['title' => 'required|max:200', 'description' => 'nullable|max:2000', 'projectId' => 'required|integer', 'entryDate' => 'nullable|date', 'assignedUserId' => 'required|integer', 'status' => 'required|in:todo,in_progress,done']);
        abort_unless($this->projects()->whereKey($this->projectId)->exists() && $this->users()->whereKey($this->assignedUserId)->exists(), 403);
        $values = ['title' => $this->title, 'description' => $this->description ?: null, 'project_id' => $this->projectId, 'due_date' => $this->entryDate ?: null, 'assigned_user_id' => $this->assignedUserId, 'status' => $this->status];
        $this->taskId ? $this->workspaceTasks()->findOrFail($this->taskId)->update($values) : Task::create($values);
        Flux::modal('task-editor')->close();
        Flux::toast($this->taskId ? 'Task updated' : 'Task created', variant: 'success');
        $this->reset('taskId', 'title', 'description', 'projectId', 'entryDate');
    }

    public function moveTask(int $taskId, string $status): void
    {
        abort_unless(in_array($status, array_column(TaskStatus::cases(), 'value'), true), 422);
        $this->workspaceTasks()->findOrFail($taskId)->update(['status' => $status]);
    }

    public function toggleTask(int $taskId): void
    {
        $task = $this->tasks()->findOrFail($taskId);
        $task->update(['status' => $task->status === TaskStatus::Done ? TaskStatus::Todo->value : TaskStatus::Done->value]);
    }

    public function prepareEvent(?string $date = null): void
    {
        $this->reset('eventId', 'title', 'description', 'endsAt', 'isPublic', 'attendeeIds');
        $this->startsAt = Carbon::parse($date ?: $this->selectedDay)->setTime(9, 0)->format('Y-m-d\TH:i');
        Flux::modal('event-editor')->show();
    }

    public function editEvent(int $eventId): void
    {
        $event = $this->calendarEvents()->with('attendees')->findOrFail($eventId);
        abort_unless($event->created_by === auth()->id(), 403);
        $this->eventId = $event->id;
        $this->title = $event->title;
        $this->description = $event->description ?? '';
        $this->startsAt = $event->starts_at->format('Y-m-d\TH:i');
        $this->endsAt = $event->ends_at?->format('Y-m-d\TH:i') ?? '';
        $this->isPublic = $event->is_public;
        $this->attendeeIds = $event->attendees->pluck('id')->all();
        Flux::modal('event-editor')->show();
    }

    public function saveEvent(): void
    {
        $this->validate(['title' => 'required|max:200', 'description' => 'nullable|max:2000', 'startsAt' => 'required|date', 'endsAt' => 'nullable|date|after_or_equal:startsAt', 'isPublic' => 'boolean', 'attendeeIds' => 'array', 'attendeeIds.*' => 'integer']);
        $companyUserIds = $this->users()->whereKey($this->attendeeIds)->pluck('id');
        abort_unless($companyUserIds->count() === count(array_unique($this->attendeeIds)), 403);
        $values = ['company_id' => auth()->user()->company_id, 'created_by' => auth()->id(), 'title' => $this->title, 'description' => $this->description ?: null, 'starts_at' => $this->startsAt, 'ends_at' => $this->endsAt ?: null, 'is_public' => $this->isPublic];
        $event = $this->eventId ? $this->calendarEvents()->where('created_by', auth()->id())->findOrFail($this->eventId) : CalendarEvent::create($values);
        $event->update($values);
        $event->attendees()->sync($companyUserIds);
        Flux::modal('event-editor')->close();
        Flux::toast($this->eventId ? 'Event updated' : 'Event created', variant: 'success');
        $this->reset('eventId', 'title', 'description', 'startsAt', 'endsAt', 'isPublic', 'attendeeIds');
    }

    public function addTicket(): void
    {
        $this->validate(['title' => 'required|max:200', 'description' => 'nullable|max:2000', 'customerId' => 'required|integer', 'projectId' => 'nullable|integer', 'assignedUserId' => 'required|integer', 'priority' => 'required|in:low,normal,high,urgent', 'category' => 'required|in:'.implode(',', array_column(TicketCategory::cases(), 'value')), 'estimatedHours' => 'required|integer|min:0|max:9999', 'estimatedMinutes' => 'required|integer|min:0|max:59']);
        abort_unless($this->customers()->whereKey($this->customerId)->exists() && $this->users()->whereKey($this->assignedUserId)->exists(), 403);
        if ($this->projectId) {
            abort_unless($this->projects()->whereKey($this->projectId)->exists(), 403);
        }
        Ticket::create(['title' => $this->title, 'description' => $this->description ?: null, 'category' => $this->category, 'customer_id' => $this->customerId, 'project_id' => $this->projectId, 'assigned_user_id' => $this->assignedUserId, 'priority' => $this->priority, 'estimated_minutes' => ($this->estimatedHours * 60) + $this->estimatedMinutes, 'time_bookmarked' => $this->timeBookmarked]);
        $this->reset('title', 'description', 'category', 'customerId', 'projectId', 'estimatedHours', 'estimatedMinutes');
        Flux::modal('create-ticket')->close();
        Flux::toast('Ticket created', variant: 'success');
    }

    public function toggleTicketBookmark(int $ticketId): void
    {
        $ticket = $this->tickets()->findOrFail($ticketId);
        $ticket->update(['time_bookmarked' => ! $ticket->time_bookmarked]);
    }

    public function bookmarkTicketForTime(int $ticketId): void
    {
        $ticket = $this->tickets()->where('assigned_user_id', auth()->id())->findOrFail($ticketId);
        $ticket->update(['time_bookmarked' => true]);
        Flux::modal('add-time-ticket')->close();
        Flux::toast('Ticket added to time tracker', variant: 'success');
    }

    public function selectCalendarDay(string $date): void
    {
        $this->selectedDay = $date;
    }

    public function changeTaskPeriod(int $amount): void
    {
        $this->calendarDate = Carbon::parse($this->calendarDate)->addMonths($amount)->toDateString();
    }

    public function changeWeek(int $weeks): void
    {
        $this->weekStart = Carbon::parse($this->weekStart)->addWeeks($weeks)->startOfWeek()->toDateString();
    }

    public function openTimeEntry(int $ticketId, string $date): void
    {
        $ticket = $this->timeTickets()->findOrFail($ticketId);
        $this->ticketId = $ticket->id;
        $this->entryDate = $date;
        $this->prepareNewTimeEntry();
        Flux::modal('log-ticket-time')->show();
    }

    public function prepareNewTimeEntry(): void
    {
        $this->timeEntryId = null;
        $this->entryHours = 0;
        $this->entryMinutes = 0;
        $this->description = '';
    }

    public function editTimeEntry(int $entryId): void
    {
        $entry = TimeEntry::where('user_id', auth()->id())->whereNotNull('booked_at')->findOrFail($entryId);
        $this->ticketId = $entry->ticket_id;
        $this->entryDate = $entry->started_at->toDateString();
        $this->timeEntryId = $entry->id;
        $this->entryHours = intdiv($entry->minutes, 60);
        $this->entryMinutes = $entry->minutes % 60;
        $this->description = $entry->description ?? '';
        Flux::modal('log-ticket-time')->show();
    }

    private function timeTickets(): Builder
    {
        return $this->tickets()->where('assigned_user_id', auth()->id())->where('time_bookmarked', true);
    }

    public function saveTicketTime(): void
    {
        $this->validate(['ticketId' => 'required|integer', 'entryDate' => 'required|date', 'entryHours' => 'required|integer|min:0|max:23', 'entryMinutes' => 'required|integer|min:0|max:59', 'description' => 'required|string|min:3|max:1000']);
        $ticket = $this->tickets()->where('assigned_user_id', auth()->id())->findOrFail($this->ticketId);
        $entryWeek = Carbon::parse($this->entryDate)->startOfWeek();
        $hasTimeInEntryWeek = TimeEntry::where('user_id', auth()->id())->where('ticket_id', $ticket->id)->whereNotNull('booked_at')->whereBetween('started_at', [$entryWeek, $entryWeek->copy()->endOfWeek()->endOfDay()])->exists();
        abort_unless($ticket->time_bookmarked || $this->timeEntryId || $hasTimeInEntryWeek, 403);
        $duration = ($this->entryHours * 60) + $this->entryMinutes;
        if ($duration < 1) {
            $this->addError('entryMinutes', 'Enter a duration greater than zero.');

            return;
        }
        if ($this->timeEntryId) {
            $entry = TimeEntry::where('user_id', auth()->id())->where('ticket_id', $ticket->id)->whereNotNull('booked_at')->findOrFail($this->timeEntryId);
            $startedAt = $entry->started_at;
            $entry->update(['description' => $this->description, 'ended_at' => $startedAt->copy()->addMinutes($duration)]);
        } else {
            $existingMinutes = TimeEntry::where('user_id', auth()->id())->where('ticket_id', $ticket->id)->whereDate('started_at', $this->entryDate)->whereNotNull('booked_at')->get()->sum->minutes;
            $startedAt = Carbon::parse($this->entryDate)->setTime(9, 0)->addMinutes($existingMinutes);
            TimeEntry::create(['user_id' => auth()->id(), 'project_id' => $ticket->project_id, 'ticket_id' => $ticket->id, 'description' => $this->description, 'started_at' => $startedAt, 'ended_at' => $startedAt->copy()->addMinutes($duration), 'booked_at' => now()]);
        }
        Flux::modal('log-ticket-time')->close();
        Flux::toast($this->timeEntryId ? 'Time entry updated' : 'Time entry added', variant: 'success');
    }

    public function startTicketTimer(int $ticketId): void
    {
        abort_if(TimeEntry::where('user_id', auth()->id())->whereNull('ended_at')->exists(), 422);
        $ticket = $this->timeTickets()->findOrFail($ticketId);
        TimeEntry::create(['user_id' => auth()->id(), 'project_id' => $ticket->project_id, 'ticket_id' => $ticket->id, 'started_at' => now()]);
        $this->dispatch('timer-updated');
        Flux::toast('Timer started', variant: 'success');
    }

    public function pauseTimer(): void
    {
        TimeEntry::where('user_id', auth()->id())->whereNull('ended_at')->latest('started_at')->firstOrFail()->update(['ended_at' => now()]);
        $this->dispatch('timer-updated');
        Flux::toast('Timer paused. Book it when ready.');
    }

    public function resumeTimer(): void
    {
        $entry = TimeEntry::where('user_id', auth()->id())->whereNotNull('ended_at')->whereNull('booked_at')->latest('ended_at')->firstOrFail();
        $pausedSeconds = $entry->ended_at->diffInSeconds(now());
        $entry->update(['started_at' => $entry->started_at->copy()->addSeconds($pausedSeconds), 'ended_at' => null]);
        $this->dispatch('timer-updated');
        Flux::toast('Timer resumed', variant: 'success');
    }

    public function openBookTimer(): void
    {
        TimeEntry::where('user_id', auth()->id())->whereNull('ended_at')->latest('started_at')->first()?->update(['ended_at' => now()]);
        $entry = TimeEntry::where('user_id', auth()->id())->whereNotNull('ended_at')->whereNull('booked_at')->latest('ended_at')->firstOrFail();
        $this->entryHours = intdiv($entry->minutes, 60);
        $this->entryMinutes = $entry->minutes % 60;
        $this->description = '';
        $this->dispatch('timer-updated');
        Flux::modal('book-timer')->show();
    }

    public function bookTimer(): void
    {
        $this->validate(['description' => 'required|string|min:3|max:1000']);
        TimeEntry::where('user_id', auth()->id())->whereNotNull('ended_at')->whereNull('booked_at')->latest('ended_at')->firstOrFail()->update(['description' => $this->description, 'booked_at' => now()]);
        $this->dispatch('timer-updated');
        Flux::modal('book-timer')->close();
        Flux::toast('Time booked', variant: 'success');
    }

    #[On('timer-updated')]
    public function refreshTimerState(): void {}

    public function render()
    {
        $customers = $this->customers()->withCount(['projects', 'tickets'])->latest()->get();
        $projects = $this->projects()->with('customer')->withCount(['tasks', 'tickets'])->latest()->get();
        $tasks = $this->tasks()->with(['project.customer', 'assignedUser'])->orderByRaw('due_date IS NULL')->orderBy('due_date')->get();
        $visibleTasks = $this->showCompletedTasks ? $tasks : $tasks->reject(fn (Task $task) => $task->status === TaskStatus::Done);
        $tickets = $this->tickets()->with(['customer', 'project', 'assignedUser', 'timeEntries' => fn ($query) => $query->whereNotNull('booked_at')])->get()->sortByDesc(fn (Ticket $ticket) => $this->ticketOrder === 'priority' ? $ticket->priority->weight() : $ticket->created_at->timestamp)->values();
        $weekStart = Carbon::parse($this->weekStart)->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek()->endOfDay();
        $timeTickets = $this->tickets()->where('assigned_user_id', auth()->id())->where(function (Builder $query) use ($weekStart, $weekEnd) {
            $query->where('time_bookmarked', true)->orWhereHas('timeEntries', fn (Builder $entries) => $entries->where('user_id', auth()->id())->whereNotNull('booked_at')->whereBetween('started_at', [$weekStart, $weekEnd]));
        })->with(['customer', 'project'])->get();
        $availableTimeTickets = $this->tickets()->where('assigned_user_id', auth()->id())->where('time_bookmarked', false)->with(['customer', 'project'])->get();
        $entries = TimeEntry::with(['project', 'task', 'ticket'])->where('user_id', auth()->id())->whereNotNull('booked_at')->latest('started_at')->take(50)->get();
        $activeTimer = TimeEntry::with('ticket')->where('user_id', auth()->id())->whereNull('ended_at')->latest('started_at')->first();
        $pausedTimer = TimeEntry::with('ticket')->where('user_id', auth()->id())->whereNotNull('ended_at')->whereNull('booked_at')->latest('ended_at')->first();
        $focus = Carbon::parse($this->calendarDate);
        $month = $focus->copy()->startOfMonth();
        $calendarDays = collect(range(0, 41))->map(fn (int $i) => $month->copy()->startOfWeek()->addDays($i));
        $taskWeek = collect(range(0, 6))->map(fn (int $i) => $focus->copy()->startOfWeek()->addDays($i));
        $weekDays = collect(range(0, 6))->map(fn (int $i) => $weekStart->copy()->addDays($i));
        $weekEntries = TimeEntry::where('user_id', auth()->id())->whereNotNull('booked_at')->whereBetween('started_at', [$weekStart->copy()->startOfDay(), $weekStart->copy()->endOfWeek()->endOfDay()])->get();
        $users = $this->users()->get();
        $calendarEvents = $this->calendarEvents()->with(['creator', 'attendees'])->whereBetween('starts_at', [$calendarDays->first()->copy()->startOfDay(), $calendarDays->last()->copy()->endOfDay()])->get();
        $shownCustomer = $this->section === 'customer' ? $this->customers()->with(['projects', 'tickets'])->find($this->showId) : null;
        $shownProject = $this->section === 'project' ? $this->projects()->with(['customer', 'tasks.assignedUser', 'tickets'])->find($this->showId) : null;
        $shownTicket = $this->section === 'ticket' ? $this->tickets()->with(['customer', 'project', 'assignedUser', 'timeEntries'])->find($this->showId) : null;
        $upcomingTasks = $tasks->reject(fn (Task $task) => $task->status === TaskStatus::Done)->filter(fn (Task $task) => $task->due_date?->between(today(), today()->addDays(14)))->take(6);
        $bookedDays = $entries->groupBy(fn (TimeEntry $entry) => $entry->started_at->toDateString())->take(7);
        $selectedTimeEntries = ($this->ticketId && $this->entryDate) ? TimeEntry::where('user_id', auth()->id())->where('ticket_id', $this->ticketId)->whereDate('started_at', $this->entryDate)->whereNotNull('booked_at')->orderBy('started_at')->get() : collect();

        return view('livewire.workspace', compact('customers', 'projects', 'tasks', 'visibleTasks', 'tickets', 'timeTickets', 'availableTimeTickets', 'entries', 'activeTimer', 'pausedTimer', 'month', 'calendarDays', 'taskWeek', 'weekDays', 'weekEntries', 'users', 'calendarEvents', 'shownCustomer', 'shownProject', 'shownTicket', 'upcomingTasks', 'bookedDays', 'selectedTimeEntries') + ['minutesToday' => $entries->filter(fn ($entry) => $entry->started_at->isToday())->sum->minutes]);
    }
}
