<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Glavo') }}</title>
    <link rel="icon" href="/favicon.svg?v=2" type="image/svg+xml">
    <link rel="icon" href="/favicon.ico?v=2" sizes="any">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=geist:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css'])
    @livewireStyles
</head>

<body class="min-h-screen font-sans" style="background:#1A1A1B; color:#F3F4F6">

    {{-- Top nav --}}
    <header class="sticky top-0 z-10 px-6 py-3 flex items-center"
        style="background:rgba(26,26,27,0.85); backdrop-filter:blur(12px); border-bottom:1px solid rgba(255,255,255,0.06);">
        {{-- Left spacer (mirrors right side to keep logo centered) --}}
        <div class="flex-1"></div>

        <a href="/" class="flex items-center gap-2">
            <img src="/images/logo.svg" alt="Glavo" class="h-8 w-auto">
        </a>

        <div class="flex-1 flex justify-end">
            @auth
                <div class="flex items-center gap-3 text-sm">
                    @if (auth()->user()->isTeacher() || auth()->user()->isAdmin())
                        <a href="{{ route('teacher.dashboard') }}" class="transition hover:opacity-80"
                            style="color:#A1A1AA">Docent</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="transition hover:opacity-70" style="color:#52525B">Uitloggen</button>
                    </form>
                </div>
            @endauth
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-10">
        @auth
            <h2 class="text-2xl font-semibold mb-6" style="color:#F3F4F6">Kies een cursus</h2>

            @php $visibleCourses = isset($courses) ? $courses->filter(fn($c) => $c->lessons->isNotEmpty()) : collect(); @endphp

            @if ($visibleCourses->isNotEmpty())
                <div class="grid gap-4">
                    @foreach ($visibleCourses as $course)
                        <div x-data="{ open: false }" class="rounded-2xl shadow-2xl overflow-hidden"
                            style="background:#2D2D30; border:1px solid rgba(255,255,255,0.06)">
                            {{-- Course header (clickable to toggle) --}}
                            <button type="button" @click="open = !open"
                                class="w-full flex items-center justify-between px-6 py-4 text-left transition hover:bg-white/5"
                                style="border-bottom:1px solid rgba(255,255,255,0.06)">
                                <div>
                                    <div class="font-semibold text-lg" style="color:#F3F4F6">{{ $course->title }}</div>
                                    <div class="text-xs mt-0.5" style="color:#71717A">
                                        {{ $course->lessons->count() }}
                                        {{ $course->lessons->count() === 1 ? 'les' : 'lessen' }}
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('learn.course', $course->id) }}" @click.stop
                                        class="btn-glavo text-sm">
                                        Alles oefenen
                                    </a>
                                    <svg :class="open ? 'rotate-180' : ''"
                                        class="w-4 h-4 transition-transform duration-200 shrink-0" style="color:#71717A"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>

                            {{-- Lessons list --}}
                            <ul x-show="open" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 -translate-y-1" class="divide-y"
                                style="border-color:rgba(255,255,255,0.04)">
                                @foreach ($course->lessons as $lesson)
                                    <li class="flex items-center justify-between px-6 py-3 transition hover:bg-white/5">
                                        <span style="color:#D4D4D8">{{ $lesson->title }}</span>
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('learn.lesson', $lesson->id) }}"
                                                class="px-4 py-1.5 rounded-xl font-semibold text-sm transition hover:scale-105 active:scale-95"
                                                style="background:rgba(16,185,129,0.15); color:#10B981; border:1px solid rgba(16,185,129,0.25)">
                                                Start les →
                                            </a>
                                            <form method="POST" action="{{ route('learn.lesson.reset', $lesson->id) }}"
                                                onsubmit="return confirm('Voortgang voor \'{{ addslashes($lesson->title) }}\' resetten? Dit zet mastery terug naar 0.')">
                                                @csrf
                                                <button type="submit"
                                                    class="flex items-center justify-center w-8 h-8 rounded-xl text-base transition hover:scale-105 active:scale-95"
                                                    style="background:rgba(239,68,68,0.10); color:#EF4444; border:1px solid rgba(239,68,68,0.2)"
                                                    title="Voortgang resetten">
                                                    ↺
                                                </button>
                                            </form>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-20" style="color:#71717A">
                    <p class="text-lg" style="color:#A1A1AA">Er zijn nog geen cursussen beschikbaar.</p>
                    <p class="text-sm mt-1">Vraag je docent om cursussen toe te voegen.</p>
                </div>
            @endif
        @else
            {{-- Logged-out hero --}}
            <div class="text-center py-24">
                <h1 class="text-4xl font-bold tracking-tight mb-3" style="color:#F3F4F6">
                    Leer een nieuwe taal
                </h1>
                <p class="text-lg mb-8" style="color:#71717A">Flashcards, spaced repetition en dagelijkse streaks.</p>
                <div class="flex justify-center gap-4">
                    <a href="{{ route('login') }}" class="btn-glavo">Login</a>
                    <a href="{{ route('register') }}" class="px-6 py-3 rounded-xl font-semibold transition hover:scale-105"
                        style="background:rgba(255,255,255,0.06); color:#F3F4F6; border:1px solid rgba(255,255,255,0.10)">Registreer</a>
                </div>
            </div>
        @endauth
    </main>

    @livewireScripts
</body>

</html>
