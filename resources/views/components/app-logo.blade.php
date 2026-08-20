@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand :name="config('app.name', 'Laravel')" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-7 items-center justify-center">
            <x-app-logo-icon class="size-7 rounded-md object-contain" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="config('app.name', 'Laravel')" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-7 items-center justify-center">
            <x-app-logo-icon class="size-7 rounded-md object-contain" />
        </x-slot>
    </flux:brand>
@endif
