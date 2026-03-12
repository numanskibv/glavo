<div class="min-h-screen flex flex-col items-center justify-between p-6 font-sans"
    style="background:#1A1A1B; color:#F3F4F6">
    <div class="w-full max-w-md">

        {{-- Header --}}
        <div class="mb-5 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-1 text-xs hover:opacity-80 transition"
                style="color:#A1A1AA">
                <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                        clip-rule="evenodd" />
                </svg>
                Cursussen
            </a>
            @if ($contextLabel)
                <span class="text-xs font-semibold tracking-wide" style="color:#FFD400">{{ $contextLabel }}</span>
            @endif
            <div class="flex items-center gap-3">
                <span class="text-xs" style="color:#52525B">{{ count($this->drawPile) + ($this->current ? 1 : 0) }}
                    kaarten</span>
                <button onclick="document.getElementById('help-modal').classList.remove('hidden')"
                    class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold transition hover:opacity-80"
                    style="background:rgba(255,255,255,0.08); color:#A1A1AA; border:1px solid rgba(255,255,255,0.12)"
                    title="Hoe werkt het?">i</button>
                <button onclick="document.getElementById('log-modal').classList.remove('hidden')"
                    class="w-6 h-6 rounded-full flex items-center justify-center transition hover:opacity-80 relative"
                    style="background:rgba(255,255,255,0.08); color:#A1A1AA; border:1px solid rgba(255,255,255,0.12)"
                    title="Mijn log">
                    <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                        <path fill-rule="evenodd"
                            d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                            clip-rule="evenodd" />
                    </svg>
                    @if (count($sessionLog) > 0)
                        <span
                            class="absolute -top-1 -right-1 w-3.5 h-3.5 rounded-full text-[9px] font-bold flex items-center justify-center"
                            style="background:#FFD400; color:#000">{{ count($sessionLog) }}</span>
                    @endif
                </button>
            </div>
        </div>

        {{-- Log modal --}}
        <div id="log-modal"
            class="hidden fixed inset-0 z-50 flex items-end justify-center px-4 pb-0 sm:items-center sm:pb-0"
            style="background:rgba(0,0,0,0.7); backdrop-filter:blur(4px)"
            onclick="if(event.target===this) this.classList.add('hidden')">
            <div class="w-full max-w-sm rounded-t-3xl sm:rounded-3xl shadow-2xl flex flex-col"
                style="background:#2D2D30; border:1px solid rgba(255,255,255,0.08); max-height:80vh">

                {{-- Modal header --}}
                <div class="flex items-center justify-between px-6 pt-6 pb-4 flex-shrink-0"
                    style="border-bottom:1px solid rgba(255,255,255,0.06)">
                    <div>
                        <h2 class="font-bold text-lg" style="color:#F3F4F6">Sessie log</h2>
                        @if (count($sessionLog) > 0)
                            @php
                                $correct = collect($sessionLog)->where('ok', true)->count();
                                $total = count($sessionLog);
                            @endphp
                            <p class="text-xs mt-0.5" style="color:#71717A">
                                {{ $correct }}/{{ $total }} goed
                                ({{ $total > 0 ? round(($correct / $total) * 100) : 0 }}%)
                            </p>
                        @endif
                    </div>
                    <button onclick="document.getElementById('log-modal').classList.add('hidden')"
                        class="w-8 h-8 rounded-full flex items-center justify-center transition hover:opacity-70"
                        style="background:rgba(255,255,255,0.06); color:#A1A1AA">✕</button>
                </div>

                {{-- Log entries --}}
                <div class="overflow-y-auto px-4 py-3 space-y-2 flex-1">
                    @forelse(array_reverse($sessionLog) as $entry)
                        <div class="flex items-center gap-3 rounded-2xl px-4 py-3"
                            style="background:{{ $entry['ok'] ? 'rgba(16,185,129,0.08)' : 'rgba(239,68,68,0.08)' }};
                                   border:1px solid {{ $entry['ok'] ? 'rgba(16,185,129,0.2)' : 'rgba(239,68,68,0.2)' }}">
                            {{-- Icon --}}
                            <span
                                class="w-7 h-7 rounded-xl flex items-center justify-center text-sm font-bold flex-shrink-0 text-white"
                                style="background:{{ $entry['ok'] ? '#10B981' : '#EF4444' }}">
                                {{ $entry['ok'] ? '✓' : '✕' }}
                            </span>
                            {{-- Words --}}
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-sm truncate" style="color:#F3F4F6">
                                    {{ $entry['dutch'] }}
                                </div>
                                <div class="text-xs mt-0.5" style="color:#A1A1AA">
                                    @if ($entry['was_pair'])
                                        {{-- Correct pair shown --}}
                                        <span style="color:#FFD400">{{ $entry['shown_bg'] }}</span>
                                        @if (!$entry['ok'])
                                            <span style="color:#52525B"> · juiste: </span>
                                            <span style="color:#10B981">{{ $entry['correct_bg'] }}</span>
                                        @endif
                                    @else
                                        {{-- Decoy shown --}}
                                        <span style="color:#{{ $entry['ok'] ? 'A1A1AA' : 'EF4444' }}">
                                            {{ $entry['shown_bg'] }}
                                        </span>
                                        <span style="color:#52525B"> (lokaas)</span>
                                        @if (!$entry['ok'])
                                            <span style="color:#52525B"> · juiste: </span>
                                            <span style="color:#10B981">{{ $entry['correct_bg'] }}</span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12" style="color:#52525B">
                            <div class="text-3xl mb-2">📋</div>
                            <div class="text-sm">Nog geen antwoorden gegeven.</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Help modal --}}
        <div id="help-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center px-6"
            style="background:rgba(0,0,0,0.7); backdrop-filter:blur(4px)"
            onclick="if(event.target===this) this.classList.add('hidden')">
            <div class="w-full max-w-sm rounded-3xl p-7 shadow-2xl"
                style="background:#2D2D30; border:1px solid rgba(255,255,255,0.08)">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="font-bold text-lg" style="color:#F3F4F6">Hoe werkt het?</h2>
                    <button onclick="document.getElementById('help-modal').classList.add('hidden')"
                        class="w-8 h-8 rounded-full flex items-center justify-center transition hover:opacity-70"
                        style="background:rgba(255,255,255,0.06); color:#A1A1AA">✕</button>
                </div>
                <div class="space-y-4 text-sm leading-relaxed" style="color:#D4D4D8">
                    <p>Je ziet een <span class="font-semibold" style="color:#F3F4F6">Nederlands woord</span> en
                        een voorgestelde <span class="font-semibold" style="color:#FFD400">Bulgaarse
                            vertaling</span>.</p>
                    <p>Soms is de vertaling <span class="font-semibold" style="color:#10B981">juist</span>,
                        soms is het een <span class="font-semibold" style="color:#EF4444">lokaas</span> — een
                        willekeurig ander woord.</p>
                    <div class="rounded-2xl p-4 space-y-2" style="background:rgba(255,255,255,0.04)">
                        <div class="flex items-center gap-3">
                            <span
                                class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-white flex-shrink-0"
                                style="background:#10B981">✓</span>
                            <span><strong style="color:#F3F4F6">Klopt</strong> — de vertaling klopt</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span
                                class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-white flex-shrink-0"
                                style="background:#EF4444">✕</span>
                            <span><strong style="color:#F3F4F6">Klopt niet</strong> — dit is een lokaas</span>
                        </div>
                    </div>
                    <p style="color:#71717A">Goed antwoord → mastery omhoog. Fout antwoord → kaart komt terug.</p>
                </div>
                <button onclick="document.getElementById('help-modal').classList.add('hidden')"
                    class="btn-glavo w-full mt-6 py-3 rounded-2xl text-center">Begrepen!</button>
            </div>
        </div>

        {{-- Card --}}
        <div id="card"
            class="relative rounded-3xl p-8 text-center shadow-2xl transition-all duration-300 ease-out"
            style="min-height:420px; background:#2D2D30; border:1px solid rgba(255,255,255,0.05); backdrop-filter:blur(12px);">

            {{-- Category badge --}}
            @if ($current && $current->term && $current->term->cluster)
                <div class="absolute left-4 top-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                        style="background:rgba(255,212,0,0.12); color:#FFD400;">{{ $current->term->cluster->title }}</span>
                </div>
            @endif

            @if ($current)
                <div class="absolute inset-0 flex flex-col items-center justify-center px-6 gap-5">

                    {{-- Dutch word (to learn) --}}
                    <div class="text-center w-full">
                        <div class="text-xs font-semibold tracking-widest uppercase mb-2" style="color:#71717A">
                            Nederlands</div>
                        <div class="text-3xl font-bold leading-tight" style="color:#F3F4F6">
                            {{ $current->term->definition ?? $current->term->word }}
                        </div>
                    </div>

                    {{-- Divider --}}
                    <div class="flex items-center gap-3 w-full">
                        <div class="flex-1 h-px" style="background:rgba(255,255,255,0.08)"></div>
                        <span class="text-xs" style="color:#3F3F46">vertaling?</span>
                        <div class="flex-1 h-px" style="background:rgba(255,255,255,0.08)"></div>
                    </div>

                    {{-- Proposed Bulgarian translation (may be a decoy) --}}
                    <div class="text-center w-full">
                        <div class="text-xs font-semibold tracking-widest uppercase mb-2" style="color:#71717A">
                            Bulgaars
                        </div>
                        <div class="text-3xl font-bold leading-tight" style="color:#FFD400">
                            {{ $shownTranslation }}
                        </div>
                    </div>

                    <div class="text-xs" style="color:#3F3F46">Klopt deze vertaling?</div>
                </div>
            @else
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="text-center">
                        <div class="text-5xl mb-4">🎉</div>
                        <div class="font-semibold text-lg" style="color:#F3F4F6">Les voltooid!</div>
                        <div class="text-sm mt-1" style="color:#A1A1AA">Alle kaarten doorlopen.</div>
                        <a href="{{ url('/') }}" class="btn-glavo inline-block mt-5">Terug naar cursussen</a>
                    </div>
                </div>
            @endif
        </div>

        {{-- Controls --}}
        <div class="mt-6 flex items-center justify-between gap-4">

            {{-- Wrong button --}}
            <button onclick="emitSwipe('left')"
                class="flex-1 py-3 rounded-2xl font-semibold text-white transition hover:scale-105 active:scale-95"
                style="background:#EF4444; box-shadow:0 4px 18px rgba(239,68,68,0.3)">
                ✕ Klopt niet
            </button>

            {{-- Efficiency ring --}}
            <div class="flex flex-col items-center flex-shrink-0">
                @php
                    $eff = (int) $this->efficiency;
                    $r = 28;
                    $circ = 2 * M_PI * $r;
                    $offset = $circ * (1 - $eff / 100);
                @endphp
                <svg class="w-16 h-16" viewBox="0 0 80 80">
                    <g transform="translate(40,40)">
                        <circle r="{{ $r }}" cx="0" cy="0" fill="none"
                            stroke="rgba(255,255,255,0.06)" stroke-width="6" />
                        <circle r="{{ $r }}" cx="0" cy="0" fill="none"
                            stroke="#FFD400" stroke-width="6" stroke-linecap="round"
                            stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $offset }}"
                            transform="rotate(-90)" style="transition:stroke-dashoffset 400ms ease" />
                        <text x="0" y="5" fill="#FFD400" font-size="13" font-weight="700"
                            text-anchor="middle">{{ $eff }}%</text>
                    </g>
                </svg>
            </div>

            {{-- Correct button --}}
            <button onclick="emitSwipe('right')"
                class="flex-1 py-3 rounded-2xl font-semibold text-white transition hover:scale-105 active:scale-95"
                style="background:#10B981; box-shadow:0 4px 18px rgba(16,185,129,0.3)">
                ✓ Klopt
            </button>
        </div>
    </div>

    <style>
        #card.swipe-good {
            box-shadow: 0 0 40px rgba(16, 185, 129, 0.45), 0 10px 30px rgba(16, 185, 129, 0.15);
            transform: translateX(22px) rotate(1.5deg);
        }

        #card.swipe-bad {
            box-shadow: 0 0 40px rgba(239, 68, 68, 0.45), 0 10px 30px rgba(239, 68, 68, 0.15);
            transform: translateX(-22px) rotate(-1.5deg);
        }

        #card.shake {
            animation: shake 0.45s ease;
        }
    </style>

    <script>
        function emitSwipe(dir) {
            if (window.Livewire) Livewire.dispatch(dir === 'right' ? 'swipeRight' : 'swipeLeft');
            const el = document.getElementById('card');
            if (!el) return;
            if (dir === 'left') {
                el.classList.add('shake');
                el.addEventListener('animationend', () => el.classList.remove('shake'), {
                    once: true
                });
            }
            el.classList.add(dir === 'right' ? 'swipe-good' : 'swipe-bad');
            setTimeout(() => el.classList.remove('swipe-good', 'swipe-bad'), 350);
        }

        (function() {
            const el = document.getElementById('card');
            if (!el) return;
            let startX = 0;

            el.addEventListener('touchstart', e => {
                startX = e.touches[0].clientX;
            }, {
                passive: true
            });

            el.addEventListener('touchmove', e => {
                const dx = e.touches[0].clientX - startX;
                if (dx > 20) {
                    el.classList.add('swipe-good');
                    el.classList.remove('swipe-bad');
                } else if (dx < -20) {
                    el.classList.add('swipe-bad');
                    el.classList.remove('swipe-good');
                } else {
                    el.classList.remove('swipe-good', 'swipe-bad');
                }
            }, {
                passive: true
            });

            el.addEventListener('touchend', e => {
                const dx = e.changedTouches[0].clientX - startX;
                el.classList.remove('swipe-good', 'swipe-bad');
                if (Math.abs(dx) >= 40) emitSwipe(dx > 0 ? 'right' : 'left');
            }, {
                passive: true
            });
        })();
    </script>
</div>
