<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden mr-2" icon="bars-2" inset="left" />

        <x-app-logo href="{{ route('dashboard') }}" wire:navigate />

        <flux:navbar class="-mb-px max-lg:hidden">
            <flux:navbar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                wire:navigate>
                {{ __('Dashboard') }}
            </flux:navbar.item>
            @if (auth()->check() && (auth()->user()->isTeacher() || auth()->user()->isAdmin()))
                <flux:navbar.item icon="book-open" :href="route('teacher.dashboard')"
                    :current="request()->routeIs('teacher*')" wire:navigate>
                    {{ __('Teacher') }}
                </flux:navbar.item>
                <flux:navbar.item icon="folder" :href="route('teacher.courses')"
                    :current="request()->routeIs('teacher.courses')" wire:navigate>
                    {{ __('Courses') }}
                </flux:navbar.item>
            @endif
        </flux:navbar>

        <flux:spacer />
        @auth
            <div class="me-3 hidden max-lg:block"></div>
        @endauth

        <flux:navbar class="me-1.5 space-x-0.5 rtl:space-x-reverse py-0!">
            <flux:tooltip :content="__('Search')" position="bottom">
                <flux:navbar.item class="!h-10 [&>div>svg]:size-5" icon="magnifying-glass" href="#"
                    :label="__('Search')" />
            </flux:tooltip>
            <flux:tooltip :content="__('Repository')" position="bottom">
                <flux:navbar.item class="h-10 max-lg:hidden [&>div>svg]:size-5" icon="folder-git-2"
                    href="https://github.com/laravel/livewire-starter-kit" target="_blank" :label="__('Repository')" />
            </flux:tooltip>
            <flux:tooltip :content="__('Documentation')" position="bottom">
                <flux:navbar.item class="h-10 max-lg:hidden [&>div>svg]:size-5" icon="book-open-text"
                    href="https://laravel.com/docs/starter-kits#livewire" target="_blank"
                    :label="__('Documentation')" />
            </flux:tooltip>
        </flux:navbar>

        {{-- XP & Streak indicator for logged-in users --}}
        @auth
            @php $u = auth()->user(); @endphp
            <div class="hidden lg:flex items-center gap-4 me-4">
                <div class="flex items-center gap-2 bg-white/5 backdrop-blur rounded-md px-3 py-1 border border-white/10">
                    <svg class="w-5 h-5 text-yellow-300" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M12 2L14.09 8.26L20.97 8.27L15.45 11.97L17.54 18.23L12 14.54L6.46 18.23L8.55 11.97L3.03 8.27L9.91 8.26L12 2Z"
                            fill="currentColor" />
                    </svg>
                    <div class="text-sm leading-tight">
                        <div class="text-xs text-zinc-300">XP</div>
                        <div class="font-medium">{{ $u->xp ?? 0 }}</div>
                    </div>
                </div>

                <div class="flex items-center gap-2 bg-white/5 backdrop-blur rounded-md px-3 py-1 border border-white/10">
                    <svg class="w-5 h-5 text-green-400" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C6.477 2 2 6.477 2 12v6h20v-6c0-5.523-4.477-10-10-10z" fill="currentColor" />
                    </svg>
                    <div class="text-sm leading-tight">
                        <div class="text-xs text-zinc-300">Streak</div>
                        <div class="font-medium">{{ $u->daily_streak ?? 0 }}d</div>
                    </div>
                </div>
            </div>

        @endauth

        <x-desktop-user-menu />
    </flux:header>

    <!-- Mobile Menu -->
    <flux:sidebar collapsible="mobile" sticky
        class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.header>
            <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
            <flux:sidebar.collapse
                class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            <flux:sidebar.group :heading="__('Platform')">
                <flux:sidebar.item icon="layout-grid" :href="route('dashboard')"
                    :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:sidebar.item>
            </flux:sidebar.group>
        </flux:sidebar.nav>

        <flux:spacer />

        <flux:sidebar.nav>
            <flux:sidebar.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit"
                target="_blank">
                {{ __('Repository') }}
            </flux:sidebar.item>
            <flux:sidebar.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire"
                target="_blank">
                {{ __('Documentation') }}
            </flux:sidebar.item>
        </flux:sidebar.nav>
    </flux:sidebar>

    {{ $slot }}

    @livewireScripts
    @fluxScripts
</body>

</html>
