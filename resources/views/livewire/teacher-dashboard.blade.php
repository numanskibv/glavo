<div class="p-4 max-w-xl mx-auto">
    <h2 class="text-xl font-semibold text-white mb-4">Docent Dashboard</h2>

    <form wire:submit.prevent="createCourse" class="mb-4">
        <input type="text" wire:model.defer="courseTitle" placeholder="Nieuwe cursus titel"
            class="w-full p-3 rounded-lg bg-gray-800 text-white" />
        <button class="mt-2 px-4 py-2 bg-yellow-400 text-black rounded-lg">Maak cursus</button>
    </form>

    <div class="mb-6">
        <label class="block text-sm text-gray-300">Importeer woordenlijst per les (xlsx of csv)</label>

        <div class="mt-2">
            <label class="text-xs text-gray-400">Koppel aan les (optioneel)</label>
            <select wire:model="lessonId" class="mt-1 w-full p-2 rounded bg-gray-800 text-white">
                <option value="">-- Geen les geselecteerd --</option>
                @foreach ($courses as $course)
                    @if ($course->lessons->isNotEmpty())
                        <optgroup label="{{ $course->title }}">
                            @foreach ($course->lessons as $lesson)
                                <option value="{{ $lesson->id }}">{{ $lesson->title ?? 'Les ' . $lesson->id }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endif
                @endforeach
            </select>
        </div>

        <input wire:model="file" type="file" accept=".csv,.xlsx,.xls" class="mt-3" />
        @error('file')
            <div class="text-sm text-red-400 mt-2">{{ $message }}</div>
        @enderror
        <div class="mt-2">
            <button wire:click.prevent="importExcel" wire:loading.attr="disabled" wire:target="importExcel,file"
                class="px-3 py-2 bg-yellow-400 rounded">
                <span wire:loading.remove wire:target="importExcel,file">Importeer naar geselecteerde les</span>
                <span wire:loading wire:target="importExcel,file">Bezig met importeren…</span>
            </button>
        </div>
        <p class="text-xs text-gray-500 mt-2">Kolommen ondersteund: `дума` (Bulgaars), `обяснение` (Nederlands). Als
            geen les geselecteerd wordt, worden termen geïmporteerd zonder leskoppeling.</p>
        <p class="text-xs text-gray-400 mt-1">Opmerking: als je bestand een kolom `lesson` bevat, worden lessen
            automatisch aangemaakt (gebruik `category` voor cluster/trefwoordgroepen). Je kunt ook eerst een les
            selecteren om import direct aan die les te koppelen.</p>
    </div>

    <div>
        <h3 class="text-lg text-gray-200">Je cursussen</h3>
        <ul class="mt-3 space-y-2">
            @foreach ($courses as $course)
                <li class="p-3 bg-gray-900 rounded-lg">
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-white font-medium">{{ $course->title }}</div>
                            <div class="text-sm text-gray-400">{{ $course->lessons->count() }} lessen</div>
                        </div>
                        <a href="{{ route('teacher.courses') }}" class="text-yellow-400">Open</a>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>
