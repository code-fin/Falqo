<?php

namespace App\Livewire;

use App\Models\{Customer, Project, Task, Ticket, TimeEntry};
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;

class Workspace extends Component
{
    #[Url]
    public string $section = 'overview';
    public string $name = '', $email = '', $title = '', $description = '', $priority = 'normal';
    public ?int $customerId = null, $projectId = null, $taskId = null;

    private function customers(): Builder { return Customer::query()->where('user_id', auth()->id()); }
    private function projects(): Builder { return Project::query()->whereHas('customer', fn (Builder $q) => $q->where('user_id', auth()->id())); }

    public function addCustomer(): void
    {
        $data = $this->validate(['name' => 'required|max:120', 'email' => 'nullable|email']);
        $this->customers()->create($data); $this->reset('name', 'email');
    }

    public function addProject(): void
    {
        $this->validate(['name' => 'required|max:160', 'customerId' => 'required|integer', 'description' => 'nullable|max:2000']);
        abort_unless($this->customers()->whereKey($this->customerId)->exists(), 403);
        Project::create(['name' => $this->name, 'customer_id' => $this->customerId, 'description' => $this->description]);
        $this->reset('name', 'customerId', 'description');
    }

    public function addTask(): void
    {
        $this->validate(['title' => 'required|max:200', 'projectId' => 'required|integer']);
        abort_unless($this->projects()->whereKey($this->projectId)->exists(), 403);
        Task::create(['title' => $this->title, 'project_id' => $this->projectId]); $this->reset('title', 'projectId');
    }

    public function addTicket(): void
    {
        $this->validate(['title' => 'required|max:200', 'customerId' => 'required|integer', 'projectId' => 'nullable|integer', 'priority' => 'in:low,normal,high,urgent']);
        abort_unless($this->customers()->whereKey($this->customerId)->exists(), 403);
        if ($this->projectId) abort_unless($this->projects()->whereKey($this->projectId)->exists(), 403);
        Ticket::create(['title' => $this->title, 'customer_id' => $this->customerId, 'project_id' => $this->projectId, 'priority' => $this->priority]);
        $this->reset('title', 'customerId', 'projectId');
    }

    public function startTimer(): void
    {
        $this->validate(['projectId' => 'required|integer', 'taskId' => 'nullable|integer', 'description' => 'nullable|max:500']);
        abort_unless($this->projects()->whereKey($this->projectId)->exists(), 403);
        abort_if(TimeEntry::where('user_id', auth()->id())->whereNull('ended_at')->exists(), 422);
        TimeEntry::create(['user_id' => auth()->id(), 'project_id' => $this->projectId, 'task_id' => $this->taskId, 'description' => $this->description, 'started_at' => now()]);
    }

    public function stopTimer(): void { TimeEntry::where('user_id', auth()->id())->whereNull('ended_at')->latest('started_at')->first()?->update(['ended_at' => now()]); }
    public function toggleTask(Task $task): void { abort_unless($this->projects()->whereKey($task->project_id)->exists(), 403); $task->update(['status' => $task->status === 'done' ? 'todo' : 'done']); }

    public function render()
    {
        $customers = $this->customers()->withCount('projects')->latest()->get();
        $projects = $this->projects()->with('customer')->withCount(['tasks', 'tickets'])->latest()->get();
        $tasks = Task::with('project')->whereIn('project_id', $projects->pluck('id'))->latest()->get();
        $tickets = Ticket::with(['customer', 'project'])->whereIn('customer_id', $customers->pluck('id'))->latest()->get();
        $entries = TimeEntry::with(['project', 'task'])->where('user_id', auth()->id())->latest('started_at')->take(20)->get();
        return view('livewire.workspace', compact('customers', 'projects', 'tasks', 'tickets', 'entries') + ['activeTimer' => $entries->firstWhere('ended_at', null), 'minutesToday' => $entries->where('started_at', '>=', today())->sum->minutes]);
    }
}
