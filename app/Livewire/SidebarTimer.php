<?php

namespace App\Livewire;

use App\Models\TimeEntry;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

class SidebarTimer extends Component
{
    public string $description = '';

    public function pause(): void
    {
        TimeEntry::where('user_id', auth()->id())->whereNull('ended_at')->latest('started_at')->firstOrFail()->update(['ended_at' => now()]);
        $this->dispatch('timer-updated');
        Flux::toast('Timer paused. Book it when ready.');
    }

    public function resume(): void
    {
        $entry = TimeEntry::where('user_id', auth()->id())->whereNotNull('ended_at')->whereNull('booked_at')->latest('ended_at')->firstOrFail();
        $pausedSeconds = $entry->ended_at->diffInSeconds(now());
        $entry->update(['started_at' => $entry->started_at->copy()->addSeconds($pausedSeconds), 'ended_at' => null]);
        $this->dispatch('timer-updated');
        Flux::toast('Timer resumed', variant: 'success');
    }

    public function book(): void
    {
        $this->validate(['description' => 'required|string|min:3|max:1000']);
        TimeEntry::where('user_id', auth()->id())->whereNotNull('ended_at')->whereNull('booked_at')->latest('ended_at')->firstOrFail()->update(['description' => $this->description, 'booked_at' => now()]);
        $this->reset('description');
        $this->dispatch('timer-updated');
        Flux::modal('sidebar-book-timer')->close();
        Flux::toast('Time booked', variant: 'success');
    }

    #[On('timer-updated')]
    public function refreshTimerState(): void {}

    public function render()
    {
        $activeTimer = TimeEntry::with('ticket')->where('user_id', auth()->id())->whereNull('ended_at')->latest('started_at')->first();
        $pausedTimer = TimeEntry::with('ticket')->where('user_id', auth()->id())->whereNotNull('ended_at')->whereNull('booked_at')->latest('ended_at')->first();

        return view('livewire.sidebar-timer', compact('activeTimer', 'pausedTimer'));
    }
}
