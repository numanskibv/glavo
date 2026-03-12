<div class="p-4 max-w-3xl mx-auto">
    <h2 class="text-2xl font-semibold text-white mb-4">Cursusbeheer</h2>

    <form wire:submit.prevent="createCourse" class="mb-6 flex gap-2">
        <input wire:model.defer="newTitle" type="text" placeholder="Nieuwe cursus titel"
            class="flex-1 p-2 rounded bg-gray-800 text-white" />
        <button class="px-4 py-2 bg-yellow-400 text-black rounded">Maak</button>
    </form>

    <div class="space-y-4">
        @foreach ($courses as $course)
            <div class="p-4 bg-gray-900 rounded-lg">
                <div class="flex justify-between items-start">
                    <div>
                        @if ($editingId === $course->id)
                            <input wire:model.defer="editingTitle" type="text"
                                class="p-2 rounded bg-gray-800 text-white" />
                            <button wire:click.prevent="updateCourse"
                                class="ml-2 px-3 py-1 bg-green-500 rounded">Opslaan</button>
                            <button wire:click.prevent="$set('editingId', null)"
                                class="ml-2 px-3 py-1 bg-gray-700 rounded">Annuleer</button>
                        @else
                            <div class="text-white font-medium">{{ $course->title }}</div>
                        @endif
                        <div class="text-sm text-gray-400">{{ $course->lessons->count() }} lessen</div>
                    </div>

                    <div class="flex gap-2">
                        <button wire:click.prevent="startEdit({{ $course->id }})"
                            class="px-3 py-1 bg-blue-600 rounded">Bewerk</button>
                        <button
                            onclick="if(!confirm('Weet je zeker dat je deze cursus wilt verwijderen?')) return false;"
                            wire:click.prevent="deleteCourse({{ $course->id }})"
                            class="px-3 py-1 bg-red-600 rounded">Verwijder</button>
                    </div>
                </div>

                <div class="mt-3 border-t border-gray-800 pt-3">
                    <h4 class="text-sm text-gray-300 mb-2">Lessen</h4>
                    <ul class="space-y-2">
                        @foreach ($course->lessons as $lesson)
                            <li class="flex justify-between items-center bg-gray-800 p-2 rounded">
                                <div class="text-white">{{ $lesson->title }}</div>
                                <div class="flex gap-2">
                                    <a href="{{ route('teacher.lessons.edit', $lesson->id) }}"
                                        class="px-2 py-1 bg-blue-500 rounded">Bewerk</a>
                                    <button
                                        onclick="if(!confirm('Weet je zeker dat je deze les wilt verwijderen?')) return false;"
                                        wire:click.prevent="deleteLesson({{ $lesson->id }})"
                                        class="px-2 py-1 bg-red-600 rounded">Verwijder</button>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-3 flex gap-2">
                        <input wire:model.defer="lessonInputs.{{ $course->id }}" placeholder="Nieuwe les"
                            class="flex-1 p-2 rounded bg-gray-800 text-white" />
                        <div class="flex flex-col">
                            <button wire:click.prevent="addLesson({{ $course->id }})"
                                class="px-3 py-2 bg-yellow-400 rounded">Voeg les toe</button>
                            @error('lesson_' . $course->id)
                                <div class="text-sm text-red-400 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
