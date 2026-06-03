@extends('layouts.app')
@section('header_title', isset($task) ? 'Редактировать задание' : 'Создание: Тест на скорость')

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
            <h1 class="text-2xl font-black text-slate-800">Создание спринта (в общий банк)</h1>
        @endif
    </div>

    <div class="flex flex-col lg:flex-row gap-10">
        
        <div class="flex-1">
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200">

                <form action="{{ isset($task) ? route('admin.update', $task->id) : route('admin.store') }}" method="POST" enctype="multipart/form-data" id="task-form" onsubmit="return validateForm(event)">
                    @csrf 
                    @if(isset($task)) @method('PUT') @endif
                    
                    <input type="hidden" name="type" value="speed_test">
                    
                    @if(isset($lesson))
                        <input type="hidden" name="lesson_id" value="{{ $lesson->id }}">
                        <input type="hidden" name="is_homework" value="{{ request('is_homework', isset($task) ? $task->is_homework : 0) }}">
                    @endif
                    
                    <div class="mb-6 border-b border-slate-100 pb-6">
                        <h2 class="text-xs font-black text-slate-400 mb-2 uppercase tracking-wide">Название задания:</h2>
                        <input type="text" name="title" value="{{ old('title', $task->title ?? '') }}" onblur="triggerAutoSave()" class="w-full border-none p-0 focus:ring-0 outline-none font-black text-2xl text-slate-800 placeholder-slate-200" placeholder="Например: Спринт: Фрукты..." required>
                    </div>

                    <div class="mb-8 flex flex-col xl:flex-row gap-6">
                        <div class="flex-1 bg-slate-50 p-6 rounded-2xl border border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" id="has-points" class="w-5 h-5 text-blue-600 rounded border-slate-300 focus:ring-blue-500" onchange="togglePointsInput(); triggerAutoSave();" {{ (isset($task) && $task->points > 0) ? 'checked' : '' }}>
                                    <span class="font-bold text-slate-700 group-hover:text-blue-700 transition-colors">Оценивается в баллах</span>
                                </label>
                            </div>
                            <div class="w-full sm:w-28 {{ (isset($task) && $task->points > 0) ? '' : 'hidden' }} transition-all" id="points-container">
                                <input type="number" name="points" id="points-input" value="{{ old('points', $task->points ?? 0) }}" min="0" onblur="triggerAutoSave()" class="w-full text-center text-xl font-black border border-slate-200 p-2.5 rounded-xl outline-none focus:border-blue-500 bg-white shadow-sm">
                            </div>
                        </div>

                        <div class="flex-1 bg-orange-50/50 p-6 rounded-2xl border border-orange-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div>
                                <span class="font-black text-slate-800 uppercase tracking-widest text-xs block mb-1">⏱️ Время (секунды)</span>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Базовое время на 1 ответ</span>
                            </div>
                            <div class="w-full sm:w-28">
                                <input type="number" name="base_time" value="{{ isset($task) ? ($task->options['base_time'] ?? 3) : 3 }}" step="0.5" min="1" max="60" onblur="triggerAutoSave()" class="w-full text-center text-xl font-black border border-orange-200 text-orange-700 p-2.5 rounded-xl outline-none focus:ring-2 focus:ring-orange-200 focus:border-orange-400 bg-white shadow-sm" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6 flex justify-between items-end px-2 border-b border-slate-100 pb-4">
                        <div>
                            <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-1">Вопросы для спринта</h2>
                            <p class="text-xs text-slate-500">Добавьте слово или картинку, затем укажите варианты ответа.</p>
                        </div>
                    </div>
                    
                    <div id="error-message" class="hidden mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center gap-3">
                        <span class="font-bold text-xl">!</span>
                        <span class="font-medium text-sm" id="error-text"></span>
                    </div>

                    <div id="questions-container" class="space-y-6 mb-8">
                        @if(isset($task) && isset($task->options['questions']))
                            @foreach($task->options['questions'] as $qIndex => $question)
                                <div class="question-block bg-white border border-slate-200 p-6 rounded-2xl relative shadow-sm focus-within:border-blue-300 focus-within:ring-4 focus-within:ring-blue-50 transition-all group">
                                    <button type="button" onclick="this.closest('.question-block').remove(); triggerAutoSave();" class="absolute -top-3 -right-3 bg-white border border-slate-200 text-slate-300 hover:text-red-500 hover:border-red-200 w-8 h-8 rounded-full flex items-center justify-center shadow-sm transition-all opacity-0 group-hover:opacity-100 z-10" title="Удалить вопрос">✕</button>
                                    
                                    <div class="mb-6 bg-slate-50 p-5 rounded-xl border border-slate-100">
                                        <div class="flex justify-between items-center mb-4">
                                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Источник вопроса</span>
                                            <select name="questions[{{$qIndex}}][type]" class="text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-lg px-2 py-1 outline-none focus:border-blue-500" onchange="toggleQType(this, {{$qIndex}}); triggerAutoSave();">
                                                <option value="text" {{ ($question['type'] ?? 'text') === 'text' ? 'selected' : '' }}>Текст / Слово</option>
                                                <option value="image" {{ ($question['type'] ?? '') === 'image' ? 'selected' : '' }}>Картинка</option>
                                            </select>
                                        </div>
                                        <div id="q-input-{{$qIndex}}">
                                            @if(($question['type'] ?? 'text') === 'text')
                                                <input type="text" name="questions[{{$qIndex}}][text]" value="{{ $question['content'] ?? '' }}" onblur="triggerAutoSave()" class="w-full border border-slate-200 p-3 rounded-xl outline-none focus:border-blue-500 font-bold text-slate-700 bg-white shadow-sm" placeholder="Например: Apple" required>
                                            @else
                                                <div class="flex items-center gap-4 bg-white p-2 rounded-xl border border-slate-200 shadow-sm">
                                                    @if(!empty($question['content']))
                                                        <img src="{{ asset('storage/' . $question['content']) }}" class="h-12 w-12 object-cover rounded-lg border border-slate-100">
                                                    @endif
                                                    <input type="file" name="questions[{{$qIndex}}][image]" accept="image/*" onchange="triggerAutoSave()" class="w-full text-sm file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:uppercase file:tracking-wider file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer" title="Загрузить картинку">
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mb-2 px-1 flex justify-between items-center">
                                        <span class="text-[10px] font-black text-blue-500 uppercase tracking-widest">Варианты ответа</span>
                                        <span class="text-[10px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded">Отметьте правильный</span>
                                    </div>

                                    <div class="answers-container space-y-3 mb-4" id="answers-{{$qIndex}}">
                                        @foreach($question['answers'] as $aIndex => $answer)
                                            <div class="flex items-center gap-4 bg-white p-3 rounded-xl border border-slate-200 transition-all focus-within:border-blue-300 focus-within:ring-2 focus-within:ring-blue-50 shadow-sm">
                                                <input type="hidden" name="questions[{{$qIndex}}][answers][{{$aIndex}}][is_correct]" value="0">
                                                <label class="relative flex items-center justify-center cursor-pointer ml-1">
                                                    <input type="radio" name="correct_q_{{$qIndex}}" class="peer sr-only ans-marker" required onchange="setCorrect(this); triggerAutoSave();" {{ ($answer['is_correct'] ?? '0') === '1' ? 'checked' : '' }}>
                                                    <div class="w-5 h-5 rounded-full border-2 border-slate-300 peer-checked:border-blue-600 peer-checked:bg-blue-600 transition-colors shadow-inner"></div>
                                                    <svg class="absolute w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                                </label>
                                                <input type="text" name="questions[{{$qIndex}}][answers][{{$aIndex}}][text]" value="{{ $answer['text'] ?? '' }}" onblur="triggerAutoSave()" class="flex-1 border-none focus:ring-0 outline-none font-medium text-slate-700 bg-transparent" placeholder="Вариант..." required>
                                                <button type="button" onclick="this.parentElement.remove(); triggerAutoSave();" class="text-slate-300 hover:text-red-500 px-2 transition-colors">✕</button>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="button" onclick="addAnswer({{$qIndex}})" class="text-xs font-bold text-blue-500 hover:text-blue-700 transition-colors uppercase tracking-wider">+ Добавить вариант</button>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    
                    <div class="flex justify-center mb-10 border-t border-dashed border-slate-200 pt-6">
                        <button type="button" onclick="addQuestion()" class="w-full border-2 border-dashed border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-300 hover:bg-blue-50 p-4 rounded-xl font-bold transition-all flex items-center justify-center gap-2">
                            <span class="text-xl">+</span> Добавить вопрос
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
                        <div class="text-5xl mb-4">{{ $currentWidget['icon'] ?? '⚡' }}</div>
                        <span class="text-xs font-black uppercase tracking-tighter leading-tight text-blue-600">{{ $currentWidget['name'] ?? 'Спринт' }}</span>
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
    let qIndex = {{ isset($task) && isset($task->options['questions']) ? count($task->options['questions']) : 0 }};

    function togglePointsInput() {
        const cb = document.getElementById('has-points');
        const c = document.getElementById('points-container');
        const i = document.getElementById('points-input');
        if (cb.checked) { c.classList.remove('hidden'); if(i.value == 0) i.value = 10; } 
        else { c.classList.add('hidden'); i.value = 0; }
    }

    function addQuestion() {
        const html = `
            <div class="question-block bg-white border border-slate-200 p-6 rounded-2xl relative shadow-sm focus-within:border-blue-300 focus-within:ring-4 focus-within:ring-blue-50 transition-all group">
                <button type="button" onclick="this.closest('.question-block').remove(); triggerAutoSave();" class="absolute -top-3 -right-3 bg-white border border-slate-200 text-slate-300 hover:text-red-500 hover:border-red-200 w-8 h-8 rounded-full flex items-center justify-center shadow-sm transition-all opacity-0 group-hover:opacity-100 z-10" title="Удалить вопрос">✕</button>
                
                <div class="mb-6 bg-slate-50 p-5 rounded-xl border border-slate-100">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Источник вопроса</span>
                        <select name="questions[${qIndex}][type]" class="text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-lg px-2 py-1 outline-none focus:border-blue-500" onchange="toggleQType(this, ${qIndex}); triggerAutoSave();">
                            <option value="text">Текст / Слово</option>
                            <option value="image">Картинка</option>
                        </select>
                    </div>
                    <div id="q-input-${qIndex}">
                        <input type="text" name="questions[${qIndex}][text]" onblur="triggerAutoSave()" class="w-full border border-slate-200 p-3 rounded-xl outline-none focus:border-blue-500 font-bold text-slate-700 bg-white shadow-sm" placeholder="Например: Dog" required>
                    </div>
                </div>

                <div class="mb-2 px-1 flex justify-between items-center">
                    <span class="text-[10px] font-black text-blue-500 uppercase tracking-widest">Варианты ответа</span>
                    <span class="text-[10px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded">Отметьте правильный</span>
                </div>

                <div class="answers-container space-y-3 mb-4" id="answers-${qIndex}"></div>
                <button type="button" onclick="addAnswer(${qIndex})" class="text-xs font-bold text-blue-500 hover:text-blue-700 transition-colors uppercase tracking-wider">+ Добавить вариант</button>
            </div>
        `;
        document.getElementById('questions-container').insertAdjacentHTML('beforeend', html);
        addAnswer(qIndex); addAnswer(qIndex);
        qIndex++;
    }

    function toggleQType(select, index) {
        const c = document.getElementById(`q-input-${index}`);
        if (select.value === 'text') {
            c.innerHTML = `<input type="text" name="questions[${index}][text]" onblur="triggerAutoSave()" class="w-full border border-slate-200 p-3 rounded-xl outline-none focus:border-blue-500 font-bold text-slate-700 bg-white shadow-sm" placeholder="Текст..." required>`;
        } else {
            c.innerHTML = `
                <div class="flex items-center gap-4 bg-white p-2 rounded-xl border border-slate-200 shadow-sm">
                    <input type="file" name="questions[${index}][image]" accept="image/*" onchange="triggerAutoSave()" class="w-full text-sm file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:uppercase file:tracking-wider file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer" required>
                </div>
            `;
        }
    }

    function addAnswer(qId) {
        const container = document.getElementById(`answers-${qId}`);
        const aCount = container.querySelectorAll('.ans-marker').length;
        const html = `
            <div class="flex items-center gap-4 bg-white p-3 rounded-xl border border-slate-200 transition-all focus-within:border-blue-300 focus-within:ring-2 focus-within:ring-blue-50 shadow-sm">
                <input type="hidden" name="questions[${qId}][answers][${aCount}][is_correct]" value="0">
                <label class="relative flex items-center justify-center cursor-pointer ml-1">
                    <input type="radio" name="correct_q_${qId}" class="peer sr-only ans-marker" required onchange="setCorrect(this); triggerAutoSave();">
                    <div class="w-5 h-5 rounded-full border-2 border-slate-300 peer-checked:border-blue-600 peer-checked:bg-blue-600 transition-colors shadow-inner"></div>
                    <svg class="absolute w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                </label>
                <input type="text" name="questions[${qId}][answers][${aCount}][text]" onblur="triggerAutoSave()" class="flex-1 border-none focus:ring-0 outline-none font-medium text-slate-700 bg-transparent" placeholder="Вариант..." required>
                <button type="button" onclick="this.parentElement.remove(); triggerAutoSave();" class="text-slate-300 hover:text-red-500 px-2 transition-colors">✕</button>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
    }

    function setCorrect(radio) {
        const container = radio.closest('.answers-container');
        container.querySelectorAll('input[name$="[is_correct]"]').forEach(i => i.value = '0');
        radio.parentElement.querySelector('input[name$="[is_correct]"]').value = '1';
    }

    document.addEventListener('DOMContentLoaded', () => { 
        @if(!isset($task)) 
            addQuestion(); 
        @endif 
    });

    function validateForm(e) {
        const qs = document.querySelectorAll('.question-block');
        const errBox = document.getElementById('error-message');
        const errText = document.getElementById('error-text');

        if (qs.length === 0) { 
            e.preventDefault(); 
            showErr("Добавьте хотя бы один вопрос для спринта!"); 
            return false; 
        }
        
        let valid = true;
        qs.forEach((q, i) => {
            const m = q.querySelectorAll('.ans-marker');
            if (m.length < 2) { valid = false; showErr(`В вопросе ${i+1} нужно минимум 2 варианта!`); }
            let hasC = false; m.forEach(x => { if(x.checked) hasC = true; });
            if (!hasC) { valid = false; showErr(`В вопросе ${i+1} не выбран правильный ответ!`); }
        });
        
        if (!valid) { 
            e.preventDefault(); 
            return false; 
        }
        return true;
    }
    
    function showErr(t) { 
        const errBox = document.getElementById('error-message');
        errBox.classList.remove('hidden'); 
        document.getElementById('error-text').innerText = t; 
        errBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // ЛОГИКА АВТОСОХРАНЕНИЯ 
    async function triggerAutoSave() {
        @if(isset($task))
            const form = document.getElementById('task-form');
            const formData = new FormData(form);
            
            try {
                const res = await fetch("{{ route('admin.tasks.autosave', $task->id) }}", {
                    method: 'POST', // Используем POST для отправки файлов, Laravel поймет _method=PUT
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
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