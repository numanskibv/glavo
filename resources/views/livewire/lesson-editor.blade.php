<div class="p-6 max-w-3xl mx-auto">
    <h2 class="text-2xl font-semibold text-white mb-4">Bewerk les: {{ $this->lesson->title }}</h2>

    <div class="mb-6 bg-gray-900 p-4 rounded">
        <h3 class="text-lg text-gray-100">Kaarten in deze les</h3>
        <ul class="mt-3 space-y-2">
            @foreach ($flashcards as $f)
                <li class="flex justify-between items-center bg-gray-800 p-3 rounded">
                    <div>
                        <div class="font-medium">{{ $f->term->word }} <span
                                class="text-sm text-gray-400">({{ $f->term->language->code ?? '' }})</span></div>
                        <div class="text-sm text-gray-300">{{ $f->term->definition }}</div>
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="removeFlashcard({{ $f->id }})"
                            class="px-3 py-1 bg-red-600 rounded">Verwijder</button>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="mb-6 bg-gray-900 p-4 rounded">
        <h3 class="text-lg text-gray-100">Maak nieuw woord</h3>
        <div class="grid gap-2">
            <input wire:model.defer="newWord" placeholder="woord (Bulgaars)"
                class="p-2 rounded bg-gray-800 text-white" />
            <input wire:model.defer="newDefinition" placeholder="uitleg/vertaling"
                class="p-2 rounded bg-gray-800 text-white" />
            <select wire:model.defer="newLanguage" class="p-2 rounded bg-gray-800 text-white">
                @foreach ($languages as $lang)
                    <option value="{{ $lang->code }}">{{ $lang->name }} ({{ $lang->code }})</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button wire:click.prevent="addNewTerm" class="px-4 py-2 bg-yellow-400 rounded">Maak en voeg
                    toe</button>
                @error('newTerm')
                    <div class="text-red-400">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="mb-6 bg-gray-900 p-4 rounded">
        <h3 class="text-lg text-gray-100">Importeer woorden voor deze les</h3>
        <div>
            <label class="text-sm text-gray-300">Bestand (.xlsx of .csv)</label>
            <input wire:model="file" type="file" accept=".csv,.xlsx,.xls" class="mt-2" />
            <div class="mt-2">
                <button wire:click.prevent="importExcel" class="px-3 py-2 bg-yellow-400 rounded">Importeer naar deze
                    les</button>
            </div>
            @error('file')
                <div class="text-red-400 mt-2">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="mb-6 bg-gray-900 p-4 rounded">
        <h3 class="text-lg text-gray-100">Zoek bestaand woord en voeg toe</h3>
        <div class="flex gap-2">
            <input wire:model.debounce.300ms="searchTerm" wire:input="searchTerms" placeholder="Zoek woord"
                class="flex-1 p-2 rounded bg-gray-800 text-white" />
            <div class="w-40"></div>
        </div>
        <ul class="mt-3 space-y-2">
            @foreach ($searchResults as $t)
                <li class="flex justify-between items-center bg-gray-800 p-2 rounded">
                    <div>
                        <div class="font-medium">{{ $t->word }} <span
                                class="text-sm text-gray-400">({{ $t->language->code ?? '' }})</span></div>
                        <div class="text-sm text-gray-300">{{ $t->definition }}</div>
                    </div>
                    <div>
                        <button wire:click.prevent="attachTerm({{ $t->id }})"
                            class="px-3 py-1 bg-green-500 rounded">Voeg toe</button>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>
