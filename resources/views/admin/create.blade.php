@extends('layouts.app')
@section('header_title', isset($task) ? 'Редактировать задание' : 'Создание упражнения')

@section('content')
<style>
    [contenteditable=true]:empty:before { content: attr(placeholder); color: #94a3b8; pointer-events: none; display: block; }
    #custom-editor ul { list-style-type: disc; padding-left: 1.5rem; margin-top: 0.5rem; margin-bottom: 0.5rem; }
    #custom-editor ol { list-style-type: decimal; padding-left: 1.5rem; margin-top: 0.5rem; margin-bottom: 0.5rem; }
    #custom-editor b, #custom-editor strong { font-weight: 700; color: #0f172a; }
</style>

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

                <form action="{{ isset($task) ? route('admin.update', $task->id) : route('admin.store') }}" method="POST" id="task-form">
                    @csrf
                    @if(isset($task)) @method('PUT') @endif
                    
                    <input type="hidden" name="type" value="cloze">
                    
                    @if(isset($lesson))
                        <input type="hidden" name="lesson_id" value="{{ $lesson->id }}">
                        <input type="hidden" name="is_homework" value="{{ request('is_homework', isset($task) ? $task->is_homework : 0) }}">
                    @endif
                    
                    <div class="mb-6 border-b border-slate-100 pb-6">
                        <h2 class="text-xs font-black text-slate-400 mb-2 uppercase tracking-wide">Заголовок упражнения:</h2>
                        <input type="text" name="title" value="{{ old('title', $task->title ?? '') }}" onblur="triggerAutoSave()" class="w-full border-none p-0 focus:ring-0 outline-none font-black text-2xl text-slate-800 placeholder-slate-200" placeholder="Например: Вставьте пропущенные слова..." required>
                    </div>

                    <div class="mb-6 bg-slate-50 p-6 rounded-2xl border border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
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

                    <div class="mb-8">
                        <div class="border border-slate-200 rounded-2xl overflow-hidden focus-within:border-blue-500 transition-all bg-white shadow-sm">
                            <div class="bg-slate-50/80 border-b border-slate-200 px-3 py-3 flex items-center gap-1 flex-wrap">
                                <button type="button" onclick="format('bold')" class="p-2 text-slate-500 hover:bg-slate-200 rounded-lg font-bold">B</button>
                                <button type="button" onclick="format('italic')" class="p-2 text-slate-500 hover:bg-slate-200 rounded-lg font-italic italic">I</button>
                                <button type="button" onclick="format('underline')" class="p-2 text-slate-500 hover:bg-slate-200 rounded-lg underline">U</button>
                                <div class="w-px h-6 bg-slate-300 mx-2"></div>
                                <button type="button" id="wrap-btn" class="ml-auto text-sm bg-blue-100 text-blue-700 px-4 py-2 rounded-xl font-bold flex items-center gap-2 hover:bg-blue-200 transition-colors">
                                    <span>🪄 Скрыть слово</span>
                                    <span class="bg-blue-200/50 px-1.5 py-0.5 rounded text-[10px] uppercase">Ctrl+Q</span>
                                </button>
                            </div>

                            <input type="hidden" name="content" id="hidden-content">
                            <div id="custom-editor" contenteditable="true" onblur="triggerAutoSave()" class="p-6 min-h-[300px] outline-none text-lg text-slate-700 leading-relaxed bg-white" placeholder="Напишите текст задания здесь... Выделите любое слово и нажмите Ctrl+Q, чтобы превратить его в пропуск.">{!! isset($task) ? $task->content : '' !!}</div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center pt-6 border-t border-slate-100">
                        @if(isset($task))
                            <a href="{{ route('admin.destroy', $task->id) }}" class="text-slate-400 hover:text-red-500 text-xs font-black uppercase tracking-widest">Удалить</a>
                        @else
                            <div></div>
                        @endif
                        <button type="submit" onclick="saveContent()" class="bg-blue-600 text-white px-10 py-4 rounded-2xl font-black uppercase tracking-widest hover:bg-blue-700 shadow-lg shadow-blue-100 transition-all">
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
                        <div class="text-5xl mb-4">{{ $currentWidget['icon'] ?? '📝' }}</div>
                        <span class="text-xs font-black uppercase tracking-tighter leading-tight text-blue-600">{{ $currentWidget['name'] ?? 'Упражнение' }}</span>
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
        
        if (checkbox.checked) {
            container.classList.remove('hidden');
            if (input.value == 0) input.value = 10;
        } else {
            container.classList.add('hidden');
            input.value = 0;
        }
    }

    const editor = document.getElementById('custom-editor');
    const hiddenInput = document.getElementById('hidden-content');

    function format(command, value = null) {
        document.execCommand(command, false, value);
        editor.focus();
    }

    function wrapSelectedText() {
        const selection = window.getSelection();
        if (!selection.rangeCount || selection.toString().length === 0) return;
        const text = selection.toString();
        if (text.startsWith('{') && text.endsWith('}')) return;
        document.execCommand('insertText', false, '{' + text + '}');
        triggerAutoSave();
    }

    document.getElementById('wrap-btn').addEventListener('click', wrapSelectedText);

    editor.addEventListener('keydown', function(e) {
        if (e.ctrlKey || e.metaKey) {
            if (e.key.toLowerCase() === 'q' || e.key.toLowerCase() === 'й') {
                e.preventDefault();
                wrapSelectedText();
            }
        }
    });

    editor.addEventListener('paste', function(e) {
        e.preventDefault();
        const text = (e.originalEvent || e).clipboardData.getData('text/plain');
        document.execCommand('insertText', false, text);
    });

    function saveContent() {
        hiddenInput.value = editor.innerHTML;
    }

    // ЛОГИКА АВТОСОХРАНЕНИЯ 
    async function triggerAutoSave() {
        @if(isset($task))
            saveContent();
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