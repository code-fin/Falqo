<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-50 text-zinc-950 antialiased dark:bg-zinc-950 dark:text-white">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200/80 bg-white/90 backdrop-blur-xl dark:border-white/10 dark:bg-zinc-950/90">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Workspace')" class="grid">
                    @php($activeSection = request()->query('section', 'overview'))
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="$activeSection === 'overview'" wire:navigate>{{ __('Overview') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="users" :href="route('dashboard', ['section' => 'customers'])" :current="$activeSection === 'customers'" wire:navigate>{{ __('Customers') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="folder" :href="route('dashboard', ['section' => 'projects'])" :current="$activeSection === 'projects'" wire:navigate>{{ __('Projects') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="ticket" :href="route('dashboard', ['section' => 'tickets'])" :current="$activeSection === 'tickets'" wire:navigate>{{ __('Tickets') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="check-circle" :href="route('dashboard', ['section' => 'tasks'])" :current="$activeSection === 'tasks'" wire:navigate>{{ __('Tasks') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="clock" :href="route('dashboard', ['section' => 'time'])" :current="$activeSection === 'time'" wire:navigate>{{ __('Time tracker') }}</flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <div class="mx-2 mb-3 rounded-2xl border border-zinc-200/80 bg-zinc-50 p-2 dark:border-white/10 dark:bg-white/5">
                <div class="mb-1 px-2 text-[11px] font-semibold uppercase tracking-wider text-zinc-400">{{ __('Appearance') }}</div>
                <flux:radio.group x-data variant="segmented" x-model="$flux.appearance" size="sm">
                    <flux:radio value="light" icon="sun" :label="__('Light')" />
                    <flux:radio value="dark" icon="moon" :label="__('Dark')" />
                    <flux:radio value="system" icon="computer-desktop" :label="__('Auto')" />
                </flux:radio.group>
            </div>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="folder-git-2" href="https://github.com/code-fin/Falqo" target="_blank">
                    {{ __('Repository') }}
                </flux:sidebar.item>

            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
