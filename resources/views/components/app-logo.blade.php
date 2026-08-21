@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand :name="config('app.name', 'Laravel')" {{ $attributes->class('brand-wordmark') }}>
        <x-slot name="logo" class="flex aspect-square !size-[34px] items-center justify-center !rounded-[10px]">
            <x-app-logo-icon class="size-[34px] object-contain" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="config('app.name', 'Laravel')" {{ $attributes->class('brand-wordmark') }}>
        <x-slot name="logo" class="flex aspect-square !size-[34px] items-center justify-center !rounded-[10px]">
            <x-app-logo-icon class="size-[34px] object-contain" />
        </x-slot>
    </flux:brand>
@endif
