@props(['model' => 'projectIcon', 'value' => 'folder', 'label' => 'Icon'])
@php($icons = ['folder','globe-alt','shopping-bag','rocket-launch','paint-brush','code-bracket','megaphone','chart-bar','building-office','briefcase','light-bulb','wrench-screwdriver'])
<div x-data="{ open: false }" class="relative" @keydown.escape.window="open = false">
    <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-white">{{ $label }}</label>
    <button type="button" @click="open = !open" class="flex h-10 w-full items-center justify-between rounded-lg border border-zinc-200 bg-white px-3 text-sm shadow-sm dark:border-white/10 dark:bg-white/10">
        <span class="flex items-center gap-2"><flux:icon :name="$value" class="size-5 text-falqo-600" /><span>{{ str($value)->replace('-',' ')->title() }}</span></span><flux:icon name="chevron-up-down" class="size-4 text-zinc-400" />
    </button>
    <div x-cloak x-show="open" x-transition.origin.top @click.outside="open = false" class="absolute z-50 mt-2 grid w-full grid-cols-4 gap-2 rounded-xl border border-zinc-200 bg-white p-3 shadow-xl dark:border-white/10 dark:bg-zinc-900">
        @foreach($icons as $icon)<button type="button" wire:click="$set('{{ $model }}','{{ $icon }}')" @click="open=false" title="{{ str($icon)->replace('-',' ')->title() }}" class="flex aspect-square items-center justify-center rounded-lg border transition-colors hover:bg-falqo-50 dark:hover:bg-falqo-950 {{ $value===$icon?'border-falqo-500 bg-falqo-50 text-falqo-600 dark:bg-falqo-950':'border-zinc-200 dark:border-white/10' }}"><flux:icon :name="$icon" class="size-5" /></button>@endforeach
    </div>
</div>
