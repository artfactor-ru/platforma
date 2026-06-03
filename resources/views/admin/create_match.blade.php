@extends('layouts.app')
@section('header_title', isset($task) ? 'Редактировать задание' : 'Создание пар (Сопоставление)')

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

                <form action="{{ isset($task) ? route('admin.update', $task->id) : route('admin.store') }}" method="POST" enctype="multipart/form-data" id="task-form" onsubmit="return validateForm(event)">
                    @csrf 
                    @if(isset($task)) @method('PUT') @endif
                    
                    <input type="hidden" name="type" value="match">
                    
                    @if(isset($lesson))
                        <input type="hidden" name="lesson_id" value="{{ $lesson->id }}">
                        <input type="hidden" name="is_homework" value="{{ request('is_homework', isset($task) ? $task->is_homework : 0) }}">
                    @endif
                    
                    <div class="mb-6 border-b border-slate-100 pb-6">
                        <h2 class="text-xs font-black text-slate-400 mb-2 uppercase tracking-wide">Заголовок упражнения:</h2>
                        <input type="text" name="title" value="{{ old('title', $task->title ?? '') }}" onblur="triggerAutoSave()" class="w-full border-none p-0 focus:ring-0 outline-none font-black text-2xl text-slate-800 placeholder-slate-200" placeholder="Например: Сопоставьте слова и картинки..." required>
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

                    <div class="mb-6 flex justify-between items-end px-2 border-b border-slate-100 pb-4">
                        <div>
                            <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-1">Пары для сопоставления</h2>
                            <p class="text-xs text-slate-500">При показе ученику элементы правой колонки перемешаются.</p>
                        </div>
                    </div>
                    
                    <div id="error-message" class="hidden mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center gap-3">
                        <span class="font-bold text-xl">!</span>
                        <span class="font-medium text-sm" id="error-text"></span>
                    </div>

                    <div id="pairs-container" class="space-y-6 mb-6">
                        @if(isset($task) && isset($task->options['pairs']) && is_array($task->options['pairs']))
                            @foreach($task->options['pairs'] as $index => $pair)
                                <div class="pair-block bg-white border border-slate-200 p-6 rounded-2xl relative flex flex-col md:flex-row items-center gap-6 shadow-sm focus-within:border-blue-300 focus-within:ring-4 focus-within:ring-blue-50 transition-all group">
                                    <button type="button" onclick="this.closest('.pair-block').remove(); triggerAutoSave();" class="absolute -top-3 -right-3 bg-white border border-slate-200 text-slate-300 hover:text-red-500 hover:border-red-200 w-8 h-8 rounded-full flex items-center justify-center shadow-sm transition-all opacity-0 group-hover:opacity-100 z-10">✕</button>
                                    
                                    <div class="flex-1 w-full bg-slate-50 p-5 rounded-xl border border-slate-100">
                                        <div class="flex justify-between items-center mb-4">
                                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Элемент 1</span>
                                            <select name="pairs[{{ $index }}][left_type]" class="text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-lg px-2 py-1 outline-none focus:border-blue-500" onchange="toggleInputType(this, 'left', {{ $index }}); triggerAutoSave();">
                                                <option value="text" {{ ($pair['left_type'] ?? 'text') === 'text' ? 'selected' : '' }}>Текст</option>
                                                <option value="image" {{ ($pair['left_type'] ?? '') === 'image' ? 'selected' : '' }}>Картинка</option>
                                            </select>
                                        </div>
                                        <div id="left-input-{{ $index }}">
                                            @if(($pair['left_type'] ?? 'text') === 'text')
                                                <input type="text" name="pairs[{{ $index }}][left_text]" value="{{ $pair['left_content'] ?? '' }}" onblur="triggerAutoSave()" class="w-full border border-slate-200 p-3 rounded-xl focus:border-blue-500 outline-none font-bold text-slate-700 bg-white" placeholder="Например: Яблоко" required>
                                            @else
                                                <div class="flex items-center gap-3">
                                                    @if(!empty($pair['left_content']))
                                                        <img src="{{ asset('storage/' . $pair['left_content']) }}" class="h-12 w-12 object-cover rounded-lg shadow-sm border border-slate-200">
                                                    @endif
                                                    <input type="file" name="pairs[{{ $index }}][left_image]" accept="image/*" onchange="triggerAutoSave()" class="w-full text-sm file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:uppercase file:tracking-wider file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200 cursor-pointer bg-white border border-slate-200 rounded-xl p-1" title="Загрузить картинку">
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-center text-slate-300">
                                        <div class="w-10 h-10 bg-white border-2 border-slate-100 rounded-full flex items-center justify-center shadow-sm">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                                        </div>
                                    </div>

                                    <div class="flex-1 w-full bg-blue-50/50 p-5 rounded-xl border border-blue-100/50">
                                        <div class="flex justify-between items-center mb-4">
                                            <span class="text-[10px] font-black text-blue-400 uppercase tracking-widest">Пара (Элемент 2)</span>
                                            <select name="pairs[{{ $index }}][right_type]" class="text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-lg px-2 py-1 outline-none focus:border-blue-500" onchange="toggleInputType(this, 'right', {{ $index }}); triggerAutoSave();">
                                                <option value="text" {{ ($pair['right_type'] ?? 'text') === 'text' ? 'selected' : '' }}>Текст</option>
                                                <option value="image" {{ ($pair['right_type'] ?? '') === 'image' ? 'selected' : '' }}>Картинка</option>
                                            </select>
                                        </div>
                                        <div id="right-input-{{ $index }}">
                                            @if(($pair['right_type'] ?? 'text') === 'text')
                                                <input type="text" name="pairs[{{ $index }}][right_text]" value="{{ $pair['right_content'] ?? '' }}" onblur="triggerAutoSave()" class="w-full border border-blue-200 p-3 rounded-xl focus:border-blue-500 outline-none font-bold text-slate-700 bg-white shadow-sm" placeholder="Например: Apple" required>
                                            @else
                                                <div class="flex items-center gap-3">
                                                    @if(!empty($pair['right_content']))
                                                        <img src="{{ asset('storage/' . $pair['right_content']) }}" class="h-12 w-12 object-cover rounded-lg shadow-sm border border-slate-200">
                                                    @endif
                                                    <input type="file" name="pairs[{{ $index }}][right_image]" accept="image/*" onchange="triggerAutoSave()" class="w-full text-sm file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:uppercase file:tracking-wider file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200 cursor-pointer bg-white border border-slate-200 rounded-xl p-1" title="Загрузить картинку">
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                </div>
                            @endforeach
                        @endif
                    </div>

                    <div class="flex justify-center mb-10 border-t border-dashed border-slate-200 pt-6">
                        <button type="button" onclick="addPair()" class="w-full border-2 border-dashed border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-300 hover:bg-blue-50 p-4 rounded-xl font-bold transition-all flex items-center justify-center gap-2">
                            <span class="text-xl">+</span> Добавить пару
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
                        <div class="text-5xl mb-4">{{ $currentWidget['icon'] ?? '🔗' }}</div>
                        <span class="text-xs font-black uppercase tracking-tighter leading-tight text-blue-600">{{ $currentWidget['name'] ?? 'Сопоставление' }}</span>
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
    function togglePointsInput() {
        const checkbox = document.getElementById('has-points');
        const container = document.getElementById('points-container');
        const input = document.getElementById('points-input');
        if (checkbox.checked) { container.classList.remove('hidden'); if(input.value == 0) input.value = 10; } 
        else { container.classList.add('hidden'); input.value = 0; }
    }

    let pIndex = {{ isset($task) && isset($task->options['pairs']) && is_array($task->options['pairs']) ? count($task->options['pairs']) : 0 }};

    function addPair() {
        const container = document.getElementById('pairs-container');
        const pairHtml = `
            <div class="pair-block bg-white border border-slate-200 p-6 rounded-2xl relative flex flex-col md:flex-row items-center gap-6 shadow-sm focus-within:border-blue-300 focus-within:ring-4 focus-within:ring-blue-50 transition-all group">
                <button type="button" onclick="this.closest('.pair-block').remove(); triggerAutoSave();" class="absolute -top-3 -right-3 bg-white border border-slate-200 text-slate-300 hover:text-red-500 hover:border-red-200 w-8 h-8 rounded-full flex items-center justify-center shadow-sm transition-all opacity-0 group-hover:opacity-100 z-10">✕</button>
                
                <div class="flex-1 w-full bg-slate-50 p-5 rounded-xl border border-slate-100">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Элемент 1</span>
                        <select name="pairs[${pIndex}][left_type]" class="text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-lg px-2 py-1 outline-none focus:border-blue-500" onchange="toggleInputType(this, 'left', ${pIndex}); triggerAutoSave();">
                            <option value="text">Текст</option>
                            <option value="image">Картинка</option>
                        </select>
                    </div>
                    <div id="left-input-${pIndex}">
                        <input type="text" name="pairs[${pIndex}][left_text]" onblur="triggerAutoSave()" class="w-full border border-slate-200 p-3 rounded-xl focus:border-blue-500 outline-none font-bold text-slate-700 bg-white" placeholder="Например: Яблоко" required>
                    </div>
                </div>

                <div class="flex items-center justify-center text-slate-300">
                    <div class="w-10 h-10 bg-white border-2 border-slate-100 rounded-full flex items-center justify-center shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                    </div>
                </div>

                <div class="flex-1 w-full bg-blue-50/50 p-5 rounded-xl border border-blue-100/50">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-[10px] font-black text-blue-400 uppercase tracking-widest">Пара (Элемент 2)</span>
                        <select name="pairs[${pIndex}][right_type]" class="text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-lg px-2 py-1 outline-none focus:border-blue-500" onchange="toggleInputType(this, 'right', ${pIndex}); triggerAutoSave();">
                            <option value="text">Текст</option>
                            <option value="image">Картинка</option>
                        </select>
                    </div>
                    <div id="right-input-${pIndex}">
                        <input type="text" name="pairs[${pIndex}][right_text]" onblur="triggerAutoSave()" class="w-full border border-blue-200 p-3 rounded-xl focus:border-blue-500 outline-none font-bold text-slate-700 bg-white shadow-sm" placeholder="Например: Apple" required>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', pairHtml);
        pIndex++;
    }

    function toggleInputType(select, side, index) {
        const container = document.getElementById(`${side}-input-${index}`);
        const inputClass = side === 'left' ? 'border-slate-200' : 'border-blue-200 shadow-sm';
        
        if (select.value === 'text') {
            container.innerHTML = `<input type="text" name="pairs[${index}][${side}_text]" onblur="triggerAutoSave()" class="w-full border ${inputClass} p-3 rounded-xl focus:border-blue-500 outline-none font-bold text-slate-700 bg-white" placeholder="Введите текст..." required>`;
        } else {
            container.innerHTML = `<input type="file" name="pairs[${index}][${side}_image]" accept="image/*" onchange="triggerAutoSave()" class="w-full text-sm file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:uppercase file:tracking-wider file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200 cursor-pointer bg-white border border-slate-200 rounded-xl p-1" required>`;
        }
    }

    document.addEventListener('DOMContentLoaded', () => { 
        @if(!isset($task) || empty($task->options['pairs']))
            addPair(); addPair(); 
        @endif
    });

    function validateForm(e) {
        const pairs = document.querySelectorAll('.pair-block');
        if (pairs.length < 2) {
            e.preventDefault();
            const err = document.getElementById('error-message');
            err.classList.remove('hidden');
            document.getElementById('error-text').innerText = "Для задания на сопоставление нужно минимум 2 пары.";
            
            // Скроллим к ошибке
            err.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }
        return true;
    }

    // ЛОГИКА АВТОСОХРАНЕНИЯ 
    async function triggerAutoSave() {
        @if(isset($task))
            const form = document.getElementById('task-form');
            const formData = new FormData(form);
            
            try {
                const res = await fetch("{{ route('admin.tasks.autosave', $task->id) }}", {
                    method: 'POST', // При отправке FormData лучше использовать POST + _method=PUT (настроен Laravel)
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                        // При отправке файлов (FormData) браузер сам ставит нужный Content-Type
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