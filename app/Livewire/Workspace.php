<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Project;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\TimeEntry;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;

class Workspace extends Component
{
    #[Url]
    public string $section = 'overview';

    public string $name = '';

    public string $email = '';

    public string $title = '';

    public string $description = '';

    public string $priority = 'normal';

    public ?int $customerId = null;

    public ?int $projectId = null;

    public ?int $taskId = null;

    private function customers(): Builder
    {
        return Customer::query()->where('user_id', auth()->id());
    }

    private function projects(): Builder
    {
        return Project::query()->whereHas('customer', fn (Builder $q) => $q->where('user_id', auth()->id()));
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
        $this->validate(['name' => 'required|max:160', 'customerId' => 'required|integer', 'description' => 'nullable|max:2000']);
        abort_unless($this->customers()->whereKey($this->customerId)->exists(), 403);
        Project::create(['name' => $this->name, 'customer_id' => $this->customerId, 'description' => $this->description]);
        $this->reset('name', 'customerId', 'description');
        Flux::modal('create-project')->close();
        Flux::toast('Project created', variant: 'success');
    }

    public function addTask(): void
    {
        $this->validate(['title' => 'required|max:200', 'projectId' => 'required|integer']);
        abort_unless($this->projects()->whereKey($this->projectId)->exists(), 403);
        Task::create(['title' => $this->title, 'project_id' => $this->projectId]);
        $this->reset('title', 'projectId');
        Flux::modal('create-task')->close();
        Flux::toast('Task created', variant: 'success');
    }

    public function addTicket(): void
    {
        $this->validate(['title' => 'required|max:200', 'customerId' => 'required|integer', 'projectId' => 'nullable|integer', 'priority' => 'in:low,normal,high,urgent']);
        abort_unless($this->customers()->whereKey($this->customerId)->exists(), 403);
        if ($this->projectId) {
            abort_unless($this->projects()->whereKey($this->projectId)->exists(), 403);
        }
        Ticket::create(['title' => $this->title, 'customer_id' => $this->customerId, 'project_id' => $this->projectId, 'priority' => $this->priority]);
        $this->reset('title', 'customerId', 'projectId');
        Flux::modal('create-ticket')->close();
        Flux::toast('Ticket created', variant: 'success');
    }

    public function startTimer(): void
    {
        $this->validate(['projectId' => 'required|integer', 'taskId' => 'nullable|integer', 'description' => 'nullable|max:500']);
        abort_unless($this->projects()->whereKey($this->projectId)->exists(), 403);
        abort_if(TimeEntry::where('user_id', auth()->id())->whereNull('ended_at')->exists(), 422);
        TimeEntry::create(['user_id' => auth()->id(), 'project_id' => $this->projectId, 'task_id' => $this->taskId, 'description' => $this->description, 'started_at' => now()]);
        $this->reset('projectId', 'taskId', 'description');
        Flux::modal('start-timer')->close();
        Flux::toast('Timer started', variant: 'success');
    }

    public function stopTimer(): void
    {
        TimeEntry::where('user_id', auth()->id())->whereNull('ended_at')->latest('started_at')->first()?->update(['ended_at' => now()]);
        Flux::toast('Time entry saved');
    }

    public function toggleTask(Task $task): void
    {
        abort_unless($this->projects()->whereKey($task->project_id)->exists(), 403);
        $task->update(['status' => $task->status === 'done' ? 'todo' : 'done']);
    }

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
