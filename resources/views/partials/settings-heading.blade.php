@php($settingsPage = request()->routeIs('security.edit') ? __('Security') : (request()->routeIs('appearance.edit') ? __('Appearance') : __('Profile')))
<flux:breadcrumbs>
    <flux:breadcrumbs.item :href="route('dashboard')" wire:navigate><span class="inline-flex items-center gap-1.5"><flux:icon name="home" variant="outline" class="breadcrumb-home-outline size-4" /><flux:icon name="home" variant="solid" class="breadcrumb-home-solid size-4" />{{ __('Dashboard') }}</span></flux:breadcrumbs.item>
    <flux:breadcrumbs.item :href="route('profile.edit')" class="transition-colors hover:!text-falqo-600 hover:no-underline" wire:navigate>{{ __('Settings') }}</flux:breadcrumbs.item>
    <flux:breadcrumbs.item>{{ $settingsPage }}</flux:breadcrumbs.item>
</flux:breadcrumbs>

<header>
    <h2 class="text-2xl font-semibold tracking-tight sm:text-3xl">{{ __('Settings') }}</h2>
    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Manage your profile and account settings.') }}</p>
</header>
