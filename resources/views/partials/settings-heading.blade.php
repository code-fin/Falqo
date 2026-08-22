@php($settingsPage = request()->routeIs('security.edit') ? __('Security') : (request()->routeIs('appearance.edit') ? __('Appearance') : __('Profile')))
<flux:breadcrumbs>
    <flux:breadcrumbs.item :href="route('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:breadcrumbs.item>
    <flux:breadcrumbs.item :href="route('profile.edit')" class="transition-colors hover:!text-falqo-600 hover:no-underline" wire:navigate>{{ __('Settings') }}</flux:breadcrumbs.item>
    <flux:breadcrumbs.item>{{ $settingsPage }}</flux:breadcrumbs.item>
</flux:breadcrumbs>

<header>
    <h2 class="text-2xl font-semibold tracking-tight sm:text-3xl">{{ __('Settings') }}</h2>
    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Manage your profile and account settings.') }}</p>
</header>
