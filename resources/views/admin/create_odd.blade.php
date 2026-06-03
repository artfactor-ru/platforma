@extends('layouts.app')
@section('header_title', isset($task) ? 'Редактировать задание' : 'Создание: Найди лишнее')

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
            <h1 class="text-2xl font-black text-slate-800">Создание упражнения (в общий банк)</h1>
        @endif
    </div>

    <div class="flex flex-col lg:flex-row gap-10">
        
        <div class="flex-1">
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200">

                <form action="{{ isset($task) ? route('admin.update', $task->id) : route('admin.store') }}" method="POST" id="task-form" onsubmit="return validateForm(event)">
                    @csrf 
                    @if(isset($task)) @method('PUT') @endif
                    
                    <input type="hidden" name="type" value="odd_one">
                    
                    @if(isset($lesson))
                        <input type="hidden" name="lesson_id" value="{{ $lesson->id }}">
                        <input type="hidden" name="is_homework" value="{{ request('is_homework', isset($task) ? $task->is_homework : 0) }}">
                    @endif
                    
                    <div class="mb-6 border-b border-slate-100 pb-6">
                        <h2 class="text-xs font-black text-slate-400 mb-2 uppercase tracking-wide">Название задания:</h2>
                        <input type="text" name="title" value="{{ old('title', $task->title ?? '') }}" onblur="triggerAutoSave()" class="w-full border-none p-0 focus:ring-0 outline-none font-black text-2xl text-slate-800 placeholder-slate-200" placeholder="Например: Найдите лишнее слово в группе..." required>
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

                    <div class="mb-8 border-b border-slate-100 pb-8">
                        <h2 class="text-xs font-black text-slate-400 mb-2 uppercase tracking-wide">Вопрос (Опционально):</h2>
                        <input type="text" name="content" value="{{ old('content', $task->content ?? '') }}" onblur="triggerAutoSave()" class="w-full border border-slate-200 p-4 rounded-xl outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-50 bg-white font-medium text-slate-700" placeholder="Например: Какое слово в ряду лишнее по признаку одушевленности?">
                    </div>

                    <div class="mb-6 flex justify-between items-end px-2 border-b border-slate-100 pb-4">
                        <div>
                            <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-1">Элементы (Слова)</h2>
                            <p class="text-xs text-slate-500">Добавьте слова и отметьте кружочком то, которое является лишним.</p>
                        </div>
                    </div>

                    <div id="items-container" class="space-y-3 mb-6">
                        @if(isset($task) && isset($task->options['items']))
                            @foreach($task->options['items'] as $index => $item)
                                <div class="flex items-center gap-4 bg-white p-3 rounded-2xl border border-slate-200 shadow-sm transition-all focus-within:border-blue-500 focus-within:ring-4 focus-within:ring-blue-50 group">
                                    <input type="hidden" name="items[{{ $index }}][is_odd]" value="{{ $item['is_odd'] ?? '0' }}">
                                    
                                    <label class="relative flex items-center justify-center cursor-pointer ml-2">
                                        <input type="radio" name="odd_radio" class="peer sr-only answer-marker" required onchange="setOdd(this); triggerAutoSave();" {{ ($item['is_odd'] ?? '0') === '1' ? 'checked' : '' }}>
                                        <div class="w-6 h-6 rounded-full border-2 border-slate-300 peer-checked:border-blue-600 peer-checked:bg-blue-600 transition-colors shadow-inner"></div>
                                        <svg class="absolute w-4 h-4 text-white opacity-0 peer-checked:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                    </label>

                                    <input type="text" name="items[{{ $index }}][text]" value="{{ $item['text'] ?? '' }}" onblur="triggerAutoSave()" class="flex-1 bg-transparent border-none focus:ring-0 outline-none font-bold text-slate-700" required>
                                    <button type="button" onclick="this.parentElement.remove(); triggerAutoSave();" class="text-slate-300 hover:text-red-500 p-2 transition-colors mr-1">✕</button>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <div class="flex justify-center mb-10 border-t border-dashed border-slate-200 pt-6">
                        <button type="button" onclick="addItem()" class="w-full border-2 border-dashed border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-300 hover:bg-blue-50 p-4 rounded-xl font-bold transition-all flex items-center justify-center gap-2">
                            <span class="text-xl">+</span> Добавить слово
                        </button>
                    </div>

                    <div class="mb-8 p-8 bg-blue-50/50 rounded-3xl border border-blue-100">
                        <label class="flex items-center gap-3 cursor-pointer mb-6 group">
                            <input type="checkbox" name="has_explanation" id="has-explanation" class="w-6 h-6 text-blue-600 rounded-lg border-blue-300 focus:ring-blue-500 transition-colors" onchange="toggleExplanations(); triggerAutoSave();" {{ isset($task) && !empty($task->options['explanations']) ? 'checked' : '' }}>
                            <span class="font-black text-slate-800 uppercase tracking-widest text-sm group-hover:text-blue-700 transition-colors">Добавить выбор объяснения (Усложнить)</span>
                        </label>
                        
                        <div id="explanations-container" class="{{ isset($task) && !empty($task->options['explanations']) ? '' : 'hidden' }} space-y-4">
                            <p class="text-xs font-bold text-blue-600/70 uppercase tracking-wider mb-4 border-b border-blue-100 pb-2">Варианты объяснения принципа:</p>
                            
                            <div id="exp-list" class="space-y-3">
                                @if(isset($task) && !empty($task->options['explanations']))
                                    @foreach($task->options['explanations'] as $eIndex => $exp)
                                        <div class="flex items-center gap-4 bg-white p-3 rounded-2xl border border-slate-200 shadow-sm transition-all focus-within:border-blue-500 focus-within:ring-4 focus-within:ring-blue-50 group">
                                            <input type="hidden" name="explanations[{{ $eIndex }}][is_correct]" value="{{ $exp['is_correct'] ?? '0' }}">
                                            
                                            <label class="relative flex items-center justify-center cursor-pointer ml-2">
                                                <input type="radio" name="exp_radio" class="peer sr-only exp-marker" required onchange="setExpCorrect(this); triggerAutoSave();" {{ ($exp['is_correct'] ?? '0') === '1' ? 'checked' : '' }}>
                                                <div class="w-5 h-5 rounded-full border-2 border-slate-300 peer-checked:border-blue-600 peer-checked:bg-blue-600 transition-colors shadow-inner"></div>
                                                <svg class="absolute w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                            </label>

                                            <input type="text" name="explanations[{{ $eIndex }}][text]" value="{{ $exp['text'] ?? '' }}" onblur="triggerAutoSave()" class="flex-1 bg-transparent border-none focus:ring-0 outline-none font-medium text-slate-700" required>
                                            <button type="button" onclick="this.parentElement.remove(); triggerAutoSave();" class="text-slate-300 hover:text-red-500 p-2 transition-colors mr-1">✕</button>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            
                            <button type="button" onclick="addExp()" class="text-xs font-bold text-blue-500 hover:text-blue-700 transition-colors uppercase tracking-wider mt-4 block">+ Добавить вариант объяснения</button>
                        </div>
                    </div>

                    <div id="error-message" class="hidden mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center gap-3">
                        <span class="font-bold text-xl">!</span>
                        <span class="font-medium text-sm" id="error-text"></span>
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
                        <div class="text-5xl mb-4">{{ $currentWidget['icon'] ?? '🔍' }}</div>
                        <span class="text-xs font-black uppercase tracking-tighter leading-tight text-blue-600">{{ $currentWidget['name'] ?? 'Найди лишнее' }}</span>
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
    let itemIndex = {{ isset($task) && isset($task->options['items']) ? count($task->options['items']) : 0 }};
    let expIndex = {{ isset($task) && isset($task->options['explanations']) ? count($task->options['explanations']) : 0 }};
    
    function togglePointsInput() {
        const checkbox = document.getElementById('has-points');
        const input = document.getElementById('points-input');
        if (checkbox.checked) { document.getElementById('points-container').classList.remove('hidden'); if(input.value == 0) input.value = 10; } 
        else { document.getElementById('points-container').classList.add('hidden'); input.value = 0; }
    }

    function addItem() {
        const container = document.getElementById('items-container');
        const html = `
            <div class="flex items-center gap-4 bg-white p-3 rounded-2xl border border-slate-200 shadow-sm transition-all focus-within:border-blue-500 focus-within:ring-4 focus-within:ring-blue-50 group">
                <input type="hidden" name="items[${itemIndex}][is_odd]" value="0">
                <label class="relative flex items-center justify-center cursor-pointer ml-2">
                    <input type="radio" name="odd_radio" class="peer sr-only answer-marker" required onchange="setOdd(this); triggerAutoSave();">
                    <div class="w-6 h-6 rounded-full border-2 border-slate-300 peer-checked:border-blue-600 peer-checked:bg-blue-600 transition-colors shadow-inner"></div>
                    <svg class="absolute w-4 h-4 text-white opacity-0 peer-checked:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                </label>
                <input type="text" name="items[${itemIndex}][text]" onblur="triggerAutoSave()" class="flex-1 bg-transparent border-none focus:ring-0 outline-none font-bold text-slate-700" placeholder="Слово или уравнение..." required>
                <button type="button" onclick="this.parentElement.remove(); triggerAutoSave();" class="text-slate-300 hover:text-red-500 p-2 transition-colors mr-1">✕</button>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        itemIndex++;
    }

    function setOdd(radio) {
        document.querySelectorAll('input[name$="[is_odd]"]').forEach(i => i.value = '0');
        radio.parentElement.parentElement.querySelector('input[name$="[is_odd]"]').value = '1';
    }

    function toggleExplanations() {
        document.getElementById('explanations-container').classList.toggle('hidden', !document.getElementById('has-explanation').checked);
    }

    function addExp() {
        const html = `
            <div class="flex items-center gap-4 bg-white p-3 rounded-2xl border border-slate-200 shadow-sm transition-all focus-within:border-blue-500 focus-within:ring-4 focus-within:ring-blue-50 group">
                <input type="hidden" name="explanations[${expIndex}][is_correct]" value="0">
                <label class="relative flex items-center justify-center cursor-pointer ml-2">
                    <input type="radio" name="exp_radio" class="peer sr-only exp-marker" required onchange="setExpCorrect(this); triggerAutoSave();">
                    <div class="w-5 h-5 rounded-full border-2 border-slate-300 peer-checked:border-blue-600 peer-checked:bg-blue-600 transition-colors shadow-inner"></div>
                    <svg class="absolute w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                </label>
                <input type="text" name="explanations[${expIndex}][text]" onblur="triggerAutoSave()" class="flex-1 bg-transparent border-none focus:ring-0 outline-none font-medium text-slate-700" placeholder="Вариант объяснения..." required>
                <button type="button" onclick="this.parentElement.remove(); triggerAutoSave();" class="text-slate-300 hover:text-red-500 p-2 transition-colors mr-1">✕</button>
            </div>
        `;
        document.getElementById('exp-list').insertAdjacentHTML('beforeend', html);
        expIndex++;
    }

    function setExpCorrect(radio) {
        document.querySelectorAll('input[name$="[is_correct]"]').forEach(i => i.value = '0');
        radio.parentElement.parentElement.querySelector('input[name$="[is_correct]"]').value = '1';
    }

    document.addEventListener('DOMContentLoaded', () => { 
        @if(!isset($task) || empty($task->options['items']))
            addItem(); addItem(); addItem();
        @endif
        
        @if(!isset($task) || empty($task->options['explanations']))
            addExp(); addExp();
        @endif
    });

    function validateForm(e) {
        const markers = document.querySelectorAll('.answer-marker');
        const errorBox = document.getElementById('error-message');
        
        if (markers.length < 3) {
            e.preventDefault();
            showErr("Добавьте минимум 3 слова!");
            return false;
        }
        
        let hasOdd = false;
        markers.forEach(m => { if(m.checked) hasOdd = true; });
        
        if (!hasOdd) {
            e.preventDefault();
            showErr("Выберите лишнее слово кружочком!");
            return false;
        }

        if (document.getElementById('has-explanation').checked) {
            const exps = document.querySelectorAll('.exp-marker');
            if (exps.length < 2) {
                e.preventDefault();
                showErr("Добавьте минимум 2 варианта объяснения!");
                return false;
            }
            let hasCorrectExp = false;
            exps.forEach(exp => { if (exp.checked) hasCorrectExp = true; });
            if (!hasCorrectExp) {
                e.preventDefault();
                showErr("Выберите правильное объяснение кружочком!");
                return false;
            }
        }

        return true;
    }
    
    function showErr(t) { 
        const err = document.getElementById('error-message');
        err.classList.remove('hidden'); 
        document.getElementById('error-text').innerText = t; 
        err.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // ЛОГИКА АВТОСОХРАНЕНИЯ 
    async function triggerAutoSave() {
        @if(isset($task))
            const form = document.getElementById('task-form');
            const formData = new FormData(form);
            
            try {
                const res = await fetch("{{ route('admin.tasks.autosave', $task->id) }}", {
                    method: 'POST', // Используем POST с _method=PUT внутри FormData
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