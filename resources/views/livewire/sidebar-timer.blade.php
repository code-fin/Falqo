<div wire:poll.1s>
    @if($activeTimer || $pausedTimer)
        @php($timer = $activeTimer ?? $pausedTimer)
        <div class="mx-2 mb-3 rounded-xl border border-zinc-200 bg-zinc-50 p-3 dark:border-white/10 dark:bg-white/5">
            <div class="flex items-center gap-2"><span class="size-2 shrink-0 rounded-full {{ $activeTimer ? 'bg-emerald-500' : 'bg-amber-500' }}"></span><p class="text-xs font-semibold">{{ $activeTimer ? 'Timer active' : 'Timer paused' }}</p></div>
            <p class="mt-2 truncate text-xs text-zinc-500 dark:text-zinc-400">#{{ $timer->ticket_id }} {{ $timer->ticket?->title }}</p>
            <p class="mt-2 font-mono text-lg font-semibold tabular-nums">{{ gmdate('H:i:s', (int) $timer->started_at->diffInSeconds($timer->ended_at ?? now())) }}</p>
            @if($activeTimer)
                <div class="mt-3 grid grid-cols-2 gap-2"><button wire:click="pause" class="flex items-center justify-center gap-1.5 rounded-lg bg-zinc-200 px-2 py-2 text-xs font-semibold dark:bg-white/10"><flux:icon name="pause" class="size-3.5" />Pause</button><flux:modal.trigger name="sidebar-book-timer"><button class="flex w-full items-center justify-center gap-1.5 rounded-lg bg-falqo-600 px-2 py-2 text-xs font-semibold text-white"><flux:icon name="check" class="size-3.5" />Book time</button></flux:modal.trigger></div>
            @else
                <div class="mt-3 grid grid-cols-2 gap-2"><button wire:click="resume" class="flex items-center justify-center gap-1.5 rounded-lg bg-zinc-200 px-2 py-2 text-xs font-semibold dark:bg-white/10"><flux:icon name="play" class="size-3.5" />Resume</button><flux:modal.trigger name="sidebar-book-timer"><button class="flex w-full items-center justify-center gap-1.5 rounded-lg bg-falqo-600 px-2 py-2 text-xs font-semibold text-white"><flux:icon name="check" class="size-3.5" />Book time</button></flux:modal.trigger></div>
            @endif
        </div>
    @endif

    <flux:modal name="sidebar-book-timer" class="max-w-md"><form wire:submit="book" class="space-y-5"><div><flux:heading size="lg">Book paused time</flux:heading><flux:subheading>Describe why this time was spent.</flux:subheading></div><flux:textarea wire:model="description" label="Why was this time spent?" rows="4" required /><div class="flex justify-end"><flux:button type="submit" variant="primary">Book the time</flux:button></div></form></flux:modal>
</div>
