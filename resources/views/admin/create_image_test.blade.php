@extends('layouts.app')
@section('header_title', isset($task) ? 'Редактировать визуальный тест' : 'Создание визуального теста')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="max-w-[1400px] mx-auto pb-20">
    
    <div class="mb-8 flex items-center justify-between">
        @if(isset($lesson))
            <a href="{{ request('is_homework') || (isset($task) && $task->is_homework) ? route('admin.lessons.homework', $lesson->id) : route('admin.lessons.builder', $lesson->id) }}" 
               class="text-blue-500 text-sm font-bold flex items-center gap-2 hover:underline w-fit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Назад в конструктор урока
            </a>
        @else
            <a href="{{ route('dashboard') }}" class="text-slate-400 text-sm font-bold hover:text-slate-600 transition-colors hover:underline">
                ← К списку всех заданий
            </a>
        @endif

        <div id="save-status" class="text-green-500 text-sm font-black uppercase tracking-widest opacity-0 transition-opacity flex items-center gap-2">
            <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div> Сохранено
        </div>
    </div>

    <div class="mb-8">
        @if(isset($lesson))
            <div class="mb-3 flex items-center gap-3">
                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest">
                    {{ request('is_homework') || (isset($task) && $task->is_homework) ? 'Домашнее задание' : 'Классная работа' }}
                </span>
            </div>
            <h1 class="text-2xl font-black text-slate-800">
                {{ isset($task) ? 'Редактирование' : 'Создание' }} упражнения
                <span class="text-slate-400 font-medium ml-2">для урока №{{ $lesson->id }} "{{ $lesson->title }}"</span>
            </h1>
        @else
            <h1 class="text-2xl font-black text-slate-800">Создание визуального теста (в общий банк)</h1>
        @endif
    </div>

    <div class="flex flex-col lg:flex-row gap-10">
        
        <div class="flex-1">
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200">

                <form action="{{ isset($task) ? route('admin.update', $task->id) : route('admin.store') }}" method="POST" enctype="multipart/form-data" id="task-form">
                    @csrf
                    @if(isset($task)) @method('PUT') @endif
                    
                    <input type="hidden" name="type" value="image_match">
                    
                    @if(isset($lesson))
                        <input type="hidden" name="lesson_id" value="{{ $lesson->id }}">
                        <input type="hidden" name="is_homework" value="{{ request('is_homework', isset($task) ? $task->is_homework : 0) }}">
                    @endif
                    
                    <div class="mb-6 border-b border-slate-100 pb-6">
                        <h2 class="text-xs font-black text-slate-400 mb-2 uppercase tracking-wide">Заголовок упражнения:</h2>
                        <input type="text" name="title" value="{{ old('title', $task->title ?? '') }}" onblur="triggerAutoSave()" class="w-full border-none p-0 focus:ring-0 outline-none font-black text-2xl text-slate-800 placeholder-slate-200" placeholder="Например: Изучаем фрукты..." required>
                    </div>

                    <div class="mb-8 bg-slate-50 p-6 rounded-2xl border border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" id="has-points" class="w-5 h-5 text-blue-600 rounded border-slate-300 focus:ring-blue-500" onchange="togglePointsInput(); triggerAutoSave();" {{ (isset($task) && $task->points > 0) ? 'checked' : '' }}>
                                <span class="font-bold text-slate-700 group-hover:text-blue-700 transition-colors">Оценивается в баллах (Контрольная)</span>
                            </label>
                            <p class="text-xs text-slate-400 ml-8 mt-1">Если включено, ученик не увидит ответы после сдачи.</p>
                        </div>
                        
                        <div class="w-full sm:w-32 {{ (isset($task) && $task->points > 0) ? '' : 'hidden' }} transition-all" id="points-container">
                            <input type="number" name="points" id="points-input" value="{{ old('points', $task->points ?? 0) }}" min="0" onblur="triggerAutoSave()" class="w-full text-center text-xl font-black border border-slate-200 p-3 rounded-xl outline-none focus:border-blue-500 bg-white">
                        </div>
                    </div>

                    @php
                        $currentMode = isset($task) ? ($task->options['mode'] ?? 'text_to_image') : 'text_to_image';
                    @endphp

                    <div class="mb-8 flex gap-4 {{ isset($task) ? 'hidden' : '' }}">
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="image_mode" value="text_to_image" class="peer hidden" {{ $currentMode === 'text_to_image' ? 'checked' : '' }} onchange="switchMode()">
                            <div class="border border-slate-200 rounded-2xl p-6 text-center peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:ring-4 peer-checked:ring-blue-50 transition-all">
                                <span class="text-3xl block mb-2">📝 ➡️ 🖼️</span>
                                <span class="font-black text-slate-700 uppercase tracking-widest text-[10px] block">Фраза -> Картинка</span>
                            </div>
                        </label>
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="image_mode" value="image_to_text" class="peer hidden" {{ $currentMode === 'image_to_text' ? 'checked' : '' }} onchange="switchMode()">
                            <div class="border border-slate-200 rounded-2xl p-6 text-center peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:ring-4 peer-checked:ring-blue-50 transition-all">
                                <span class="text-3xl block mb-2">🖼️ ➡️ 📝</span>
                                <span class="font-black text-slate-700 uppercase tracking-widest text-[10px] block">Картинка -> Фраза</span>
                            </div>
                        </label>
                    </div>

                    @if(isset($task))
                        <input type="hidden" name="image_mode" value="{{ $currentMode }}">
                    @endif

                    <div class="mb-8 bg-slate-50 p-6 rounded-2xl border border-slate-200" id="question-zone">
                    </div>

                    <div class="mb-4 flex justify-between items-center px-2">
                        <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest">Варианты ответов</h2>
                        <span class="text-xs font-bold text-slate-400 bg-slate-100 px-3 py-1 rounded-lg">Отметьте правильный галочкой</span>
                    </div>

                    <div id="answers-container" class="space-y-3 mb-6">
                    </div>
                    
                    <div class="flex justify-center mb-10 border-t border-dashed border-slate-200 pt-6">
                        <button type="button" onclick="addAnswer()" class="w-full border-2 border-dashed border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-300 hover:bg-blue-50 p-4 rounded-xl font-bold transition-all flex items-center justify-center gap-2">
                            <span class="text-xl">+</span> Добавить вариант
                        </button>
                    </div>
                    
                    <div class="flex justify-between items-center pt-6 border-t border-slate-100">
                        @if(isset($task))
                            <a href="{{ route('admin.destroy', $task->id) }}" class="text-slate-400 hover:text-red-500 text-xs font-black uppercase tracking-widest transition-colors">Удалить</a>
                        @else
                            <div></div>
                        @endif
                        <button type="submit" class="bg-blue-600 text-white px-10 py-4 rounded-2xl font-black uppercase tracking-widest hover:bg-blue-700 shadow-lg shadow-blue-100 transition-all">
                            {{ isset($task) ? 'Обновить задание' : 'Сохранить и добавить в урок' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="w-full lg:w-[340px] flex-shrink-0">
            <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-200 sticky top-8">
                
                @if(isset($task))
                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest mb-2 text-center">Тип задания</h3>
                    <p class="text-[10px] text-slate-400 text-center mb-8 uppercase tracking-wider font-bold italic">Шаблон зафиксирован</p>
                    
                    @php $currentWidget = collect($widgetTypes)->firstWhere('id', $task->type); @endphp
                    
                    <div class="aspect-square bg-white border-2 border-blue-500 ring-4 ring-blue-50 rounded-3xl p-6 flex flex-col items-center justify-center text-center mx-auto w-2/3 shadow-lg shadow-blue-100">
                        <div class="text-5xl mb-4">{{ $currentWidget['icon'] ?? '🖼️' }}</div>
                        <span class="text-xs font-black uppercase tracking-tighter leading-tight text-blue-600">{{ $currentWidget['name'] ?? 'Визуальный тест' }}</span>
                    </div>

                    <div class="mt-8 text-center text-[10px] text-slate-400 font-bold uppercase tracking-widest leading-relaxed bg-slate-50 p-4 rounded-2xl border border-slate-100">
                        Чтобы создать задание другого типа, вернитесь в урок и нажмите <br><span class="text-slate-600">«+ Добавить»</span>
                    </div>
                @else
                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest mb-2 text-center">Сменить шаблон</h3>
                    <p class="text-[10px] text-slate-400 text-center mb-8 uppercase tracking-wider font-bold italic">Выберите другой виджет</p>
                    
                    <div class="grid grid-cols-2 gap-3">
                        @foreach($widgetTypes as $widget)
                            <a href="{{ route('admin.create', ['type' => $widget['id'], 'lesson_id' => $lesson->id ?? null, 'is_homework' => request('is_homework', 0)]) }}" 
                               class="aspect-square bg-slate-50 border-2 border-slate-100 rounded-3xl p-4 flex flex-col items-center justify-center text-center group hover:border-blue-400 hover:bg-white hover:shadow-xl transition-all {{ $type === $widget['id'] ? 'border-blue-500 bg-white ring-4 ring-blue-50' : '' }}">
                                <div class="text-3xl mb-2 {{ $type === $widget['id'] ? '' : 'grayscale group-hover:grayscale-0' }} transition-all transform group-hover:scale-110">{{ $widget['icon'] }}</div>
                                <span class="text-[10px] font-black uppercase tracking-tighter leading-tight {{ $type === $widget['id'] ? 'text-blue-600' : 'text-slate-400' }} group-hover:text-blue-600 transition-colors">{{ $widget['name'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif

            </div>
        </div>

    </div>
</div>

<script>
    let answerCount = 0;
    const isEditMode = {{ isset($task) ? 'true' : 'false' }};
    const currentMode = "{{ $currentMode }}";

    function togglePointsInput() {
        const checkbox = document.getElementById('has-points');
        const container = document.getElementById('points-container');
        const input = document.getElementById('points-input');
        if (checkbox.checked) { container.classList.remove('hidden'); if(input.value == 0) input.value = 10; } 
        else { container.classList.add('hidden'); input.value = 0; }
    }

    function switchMode() {
        const mode = document.querySelector('input[name="image_mode"]:checked')?.value || currentMode;
        const qZone = document.getElementById('question-zone');
        
        if (!isEditMode) {
            document.getElementById('answers-container').innerHTML = ''; 
            answerCount = 0;
        }

        if (mode === 'text_to_image') {
            qZone.innerHTML = `
                <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-3">Главная фраза или слово:</label>
                <input type="text" name="content" value="{{ isset($task) ? old('content', $task->content) : '' }}" onblur="triggerAutoSave()" class="w-full border border-slate-200 p-4 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none bg-white font-bold" placeholder="Например: Apple" required>
            `;
        } else {
            const oldImageHtml = isEditMode && '{{ $task->content ?? '' }}' !== '' ? `
                <div class="mb-4 flex items-center gap-4">
                    <img src="{{ asset('storage/' . ($task->content ?? '')) }}" class="h-20 w-20 object-cover rounded-xl border border-slate-200 shadow-sm">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Текущая картинка</span>
                </div>
            ` : '';

            qZone.innerHTML = `
                <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-3">Загрузите главную картинку:</label>
                ${oldImageHtml}
                <input type="file" name="main_image" accept="image/*" class="w-full bg-white border border-slate-200 p-2 rounded-xl cursor-pointer file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:uppercase file:tracking-wider file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all" ${isEditMode ? '' : 'required'}>
            `;
        }
        
        if (!isEditMode) {
            addAnswer(); addAnswer();
        }
    }

    function addAnswer(isCorrect = '0', textValue = '', imageUrl = '') {
        const mode = document.querySelector('input[name="image_mode"]:checked')?.value || currentMode;
        const container = document.getElementById('answers-container');
        
        let inputHtml = '';
        if (mode === 'text_to_image') {
            const imgPreview = imageUrl ? `<img src="{{ asset('storage') }}/${imageUrl}" class="h-12 w-12 object-cover rounded-lg shadow-sm border border-slate-200">` : '';
            const req = isEditMode && imageUrl ? '' : 'required';
            inputHtml = `
                <div class="flex items-center gap-3 flex-1">
                    ${imgPreview}
                    <input type="file" name="answers[${answerCount}][image]" accept="image/*" class="flex-1 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-100 hover:file:bg-slate-200 cursor-pointer" ${req}>
                </div>
            `;
        } else {
            inputHtml = `<input type="text" name="answers[${answerCount}][text]" value="${textValue}" placeholder="Введите текст перевода..." class="flex-1 bg-transparent border-none p-0 focus:ring-0 outline-none font-bold text-slate-700" required>`;
        }

        const checkedAttr = isCorrect === '1' ? 'checked' : '';

        const answerHtml = `
            <div class="flex items-center gap-4 bg-white p-3 rounded-2xl border border-slate-200 shadow-sm transition-all focus-within:border-blue-500 group">
                <input type="hidden" name="answers[${answerCount}][is_correct]" value="0">
                <label class="relative flex items-center justify-center cursor-pointer ml-2">
                    <input type="radio" name="correct_answer" value="${answerCount}" class="peer sr-only" required onchange="setCorrectAnswer(this); triggerAutoSave();" ${checkedAttr}>
                    <div class="w-6 h-6 rounded-full border-2 border-slate-300 peer-checked:border-blue-600 peer-checked:bg-blue-600 transition-colors shadow-inner"></div>
                    <svg class="absolute w-4 h-4 text-white opacity-0 peer-checked:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                </label>
                ${inputHtml}
                <button type="button" onclick="this.parentElement.remove(); triggerAutoSave();" class="text-slate-300 hover:text-red-500 p-2 transition-colors mr-1">✕</button>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', answerHtml);
        answerCount++;
    }

    function setCorrectAnswer(radio) {
        document.querySelectorAll('input[name$="[is_correct]"]').forEach(input => input.value = '0');
        radio.closest('div').querySelector('input[name$="[is_correct]"]').value = '1';
    }

    document.addEventListener('DOMContentLoaded', () => { 
        switchMode();

        @if(isset($task) && isset($task->options['items']))
            @foreach($task->options['items'] as $item)
                addAnswer(
                    '{{ $item['is_correct'] ?? '0' }}', 
                    '{{ addslashes($item['text'] ?? '') }}', 
                    '{{ addslashes($item['image'] ?? '') }}'
                );
            @endforeach
            
            setTimeout(() => {
                const checkedRadio = document.querySelector('input[name="correct_answer"]:checked');
                if(checkedRadio) setCorrectAnswer(checkedRadio);
            }, 100);
        @endif
    });

    // ЛОГИКА АВТОСОХРАНЕНИЯ 
    async function triggerAutoSave() {
        @if(isset($task))
            const form = document.getElementById('task-form');
            const formData = new FormData(form);
            
            try {
                const res = await fetch("{{ route('admin.tasks.autosave', $task->id) }}", {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(Object.fromEntries(formData))
                });
                
                if (res.ok) {
                    const status = document.getElementById('save-status');
                    status.classList.remove('opacity-0');
                    setTimeout(() => status.classList.add('opacity-0'), 2000);
                }
            } catch (e) { console.error('Autosave failed'); }
        @endif
    }
</script>
@endsection