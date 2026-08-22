<div wire:poll.5s>
    @if($activeTimer || $pausedTimer)
        @php($timer = $activeTimer ?? $pausedTimer)
        <div class="mx-2 mb-3 rounded-xl border border-zinc-200 bg-zinc-50 p-3 dark:border-white/10 dark:bg-white/5">
            <div class="flex items-center gap-2"><span class="size-2 shrink-0 rounded-full {{ $activeTimer ? 'bg-emerald-500' : 'bg-amber-500' }}"></span><p class="text-xs font-semibold">{{ $activeTimer ? 'Timer active' : 'Timer paused' }}</p></div>
            <p class="mt-2 truncate text-xs text-zinc-500 dark:text-zinc-400">#{{ $timer->ticket_id }} {{ $timer->ticket?->title }}</p>
            @if($activeTimer)
                <button wire:click="pause" class="mt-3 w-full rounded-lg bg-zinc-200 px-3 py-2 text-xs font-semibold dark:bg-white/10">Pause timer</button>
            @else
                <flux:modal.trigger name="sidebar-book-timer"><button class="mt-3 w-full rounded-lg bg-falqo-600 px-3 py-2 text-xs font-semibold text-white">Book the time</button></flux:modal.trigger>
            @endif
        </div>
    @endif

    <flux:modal name="sidebar-book-timer" class="max-w-md"><form wire:submit="book" class="space-y-5"><div><flux:heading size="lg">Book paused time</flux:heading><flux:subheading>Describe why this time was spent.</flux:subheading></div><flux:textarea wire:model="description" label="Why was this time spent?" rows="4" required /><div class="flex justify-end"><flux:button type="submit" variant="primary">Book the time</flux:button></div></form></flux:modal>
</div>
