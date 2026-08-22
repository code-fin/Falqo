<?php

namespace App\Livewire;

use App\Models\TimeEntry;
use Flux\Flux;
use Livewire\Component;

class SidebarTimer extends Component
{
    public string $description = '';

    public function pause(): void
    {
        TimeEntry::where('user_id', auth()->id())->whereNull('ended_at')->latest('started_at')->firstOrFail()->update(['ended_at' => now()]);
        Flux::toast('Timer paused. Book it when ready.');
    }

    public function book(): void
    {
        $this->validate(['description' => 'required|string|min:3|max:1000']);
        TimeEntry::where('user_id', auth()->id())->whereNotNull('ended_at')->whereNull('booked_at')->latest('ended_at')->firstOrFail()->update(['description' => $this->description, 'booked_at' => now()]);
        $this->reset('description');
        Flux::modal('sidebar-book-timer')->close();
        Flux::toast('Time booked', variant: 'success');
    }

    public function render()
    {
        $activeTimer = TimeEntry::with('ticket')->where('user_id', auth()->id())->whereNull('ended_at')->latest('started_at')->first();
        $pausedTimer = TimeEntry::with('ticket')->where('user_id', auth()->id())->whereNotNull('ended_at')->whereNull('booked_at')->latest('ended_at')->first();

        return view('livewire.sidebar-timer', compact('activeTimer', 'pausedTimer'));
    }
}
