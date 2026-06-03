@extends('layouts.app')
@section('header_title', 'Настройка урока: ' . $lesson->title)

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="max-w-[1400px] mx-auto pb-20">
    <div class="flex items-center justify-between mb-8">
        <a href="{{ route('admin.courses.edit', $lesson->course_id) }}" class="text-blue-600 font-bold hover:underline flex items-center gap-2">
            ← Назад в список уроков
        </a>
        <div class="flex items-center gap-4">
            <span class="text-slate-400 text-sm italic">Курс: {{ $lesson->course->title }}</span>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">
        
        <div class="flex-1 space-y-8">
            
            <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200">
                <div class="flex items-start justify-between mb-8">
                    <div>
                        <div class="flex items-center gap-3 mb-2 group">
                            <h1 class="text-3xl font-black text-slate-800 flex items-center gap-2">
                                Урок: №{{ $lesson->id }} 
                                <span id="lesson-title-display" class="border-b-2 border-transparent">{{ $lesson->title }}</span>
                            </h1>
                            <button onclick="editLessonTitleTop()" class="text-slate-300 hover:text-blue-500 text-xl transition-colors cursor-pointer" title="Редактировать название">✎</button>
                        </div>
                        <a href="{{ route('admin.lessons.homework', $lesson->id) }}" class="text-blue-500 text-sm block mb-6 hover:underline">Перейти к домашнему заданию →</a>
                    </div>
                </div>

                <form id="lesson-settings-form" class="space-y-6">
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="radio" name="is_strict_order" value="0" class="w-5 h-5 text-blue-600 focus:ring-blue-500" {{ !$lesson->is_strict_order ? 'checked' : '' }}>
                            <span class="font-bold text-slate-700 group-hover:text-blue-600 transition-colors">Произвольный порядок прохождения упражнений</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="radio" name="is_strict_order" value="1" class="w-5 h-5 text-blue-600 focus:ring-blue-500" {{ $lesson->is_strict_order ? 'checked' : '' }}>
                            <span class="font-bold text-slate-700 group-hover:text-blue-600 transition-colors">Выполнение упражнений строго в заданном порядке</span>
                        </label>
                    </div>

                    <div class="flex items-center gap-4 pt-4 border-t border-slate-100">
                        <span class="font-bold text-slate-800 whitespace-nowrap">Продолжительность урока:</span>
                        <div class="flex items-center gap-2">
                            <select id="duration_h" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 outline-none focus:ring-2 focus:ring-blue-500 font-bold">
                                @for($i=0; $i<=5; $i++) <option value="{{$i}}" {{ floor(($lesson->duration_minutes ?? 0)/60) == $i ? 'selected' : '' }}>{{$i}} час</option> @endfor
                            </select>
                            <span class="font-bold text-slate-400">:</span>
                            <select id="duration_m" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 outline-none focus:ring-2 focus:ring-blue-500 font-bold">
                                @for($i=0; $i<60; $i+=5) <option value="{{$i}}" {{ ($lesson->duration_minutes ?? 0)%60 == $i ? 'selected' : '' }}>{{$i}} мин</option> @endfor
                            </select>
                        </div>
                        <button type="button" onclick="saveLessonSettings()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-6 py-2 rounded-xl font-black uppercase text-xs tracking-widest transition-all ml-4">Сохранить</button>
                        <span id="save-indicator" class="text-green-500 text-sm font-bold opacity-0 transition-opacity ml-2">✓ Сохранено</span>
                    </div>
                </form>
            </div>

            <div id="tasks-in-lesson" class="space-y-6">
                @forelse($lesson->tasks->where('is_homework', false)->values() as $index => $task)
                    <div class="task-card bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden group transition-all" data-id="{{ $task->id }}">
                        
                        <div class="bg-slate-50/50 px-8 py-4 flex items-center justify-between border-b border-slate-100">
                            <div class="flex items-center gap-4">
                                <div class="handle cursor-grab text-slate-300 hover:text-slate-600" title="Перетащить">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path></svg>
                                </div>
                                <h3 class="task-title-display text-lg font-black text-slate-800 uppercase tracking-tight">
                                    Упражнение: №{{ $index + 1 }} <span class="text-blue-600 ml-2">"{{ $task->title }}"</span>
                                </h3>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <form action="{{ route('admin.destroy', $task->id) }}" method="POST" onsubmit="return confirm('Удалить упражнение?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-red-50 transition-colors bg-white rounded-xl border border-slate-200 shadow-sm" title="Удалить">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="p-6 bg-slate-50/30">
                            
                            <div class="flex items-center gap-3 mb-4 px-2">
                                <span class="bg-white border border-slate-200 text-slate-500 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest shadow-sm">
                                    Тип: {{ $task->type }}
                                </span>
                                <span class="bg-blue-50 border border-blue-100 text-blue-600 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest shadow-sm flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ $task->points }} баллов
                                </span>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden relative group/preview">
                                
                                <div class="bg-slate-100 border-b border-slate-200 px-4 py-2.5 flex items-center justify-between relative z-30">
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-2.5 h-2.5 rounded-full bg-slate-300"></div>
                                        <div class="w-2.5 h-2.5 rounded-full bg-slate-300"></div>
                                        <div class="w-2.5 h-2.5 rounded-full bg-slate-300"></div>
                                    </div>
                                    
                                    <div class="flex items-center gap-3">
                                        <button type="button" 
                                                onclick="toggleExpand(event, this, 'task-content-{{ $task->id }}', 'gradient-{{ $task->id }}')" 
                                                class="text-[9px] font-black text-blue-600 uppercase tracking-widest hover:bg-blue-600 hover:text-white transition-all bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm active:scale-95">
                                            Развернуть
                                        </button>
                                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Визуальный предпросмотр</span>
                                    </div>
                                </div>
                                
                                <div id="task-content-{{ $task->id }}" class="relative max-h-[250px] overflow-hidden bg-slate-50/50 transition-all duration-500 ease-in-out z-10">
                                    <div class="pointer-events-none select-none p-6">
                                        @include('admin.lessons.partials.task_preview', ['task' => $task])
                                    </div>
                                    
                                    <div id="gradient-{{ $task->id }}" class="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-t from-white via-white/90 to-transparent pointer-events-none z-10 transition-opacity duration-300"></div>
                                </div>

                                <div class="absolute inset-0 bg-slate-900/5 backdrop-blur-[2px] opacity-0 group-hover/preview:opacity-100 transition-all flex items-center justify-center z-20 cursor-pointer" 
                                     onclick="window.location.href='{{ route('admin.edit', $task->id) }}'">
                                    <span class="bg-white text-slate-800 px-8 py-4 rounded-2xl font-black uppercase text-xs tracking-widest shadow-2xl border border-slate-100 hover:scale-105 hover:text-blue-600 transition-all flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        Открыть редактор
                                    </span>
                                </div>
                            </div>

                            <script>
                            /**
                             * Исправленная функция: теперь первым аргументом передаем event
                             */
                            function toggleExpand(e, btn, contentId, gradientId) {
                                // Полностью блокируем клик для всех родительских элементов (оверлея)
                                e.preventDefault();
                                e.stopPropagation();

                                const content = document.getElementById(contentId);
                                const gradient = document.getElementById(gradientId);

                                if (content.style.maxHeight === 'none') {
                                    content.style.maxHeight = '250px';
                                    gradient.style.opacity = '1';
                                    btn.innerText = 'Развернуть';
                                } else {
                                    content.style.maxHeight = 'none';
                                    gradient.style.opacity = '0';
                                    btn.innerText = 'Свернуть';
                                }
                            }
                            </script>
                            
                        </div>
                    </div>
                @empty
                    <div class="bg-white border-2 border-dashed border-slate-200 rounded-3xl p-20 text-center text-slate-400">
                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center text-3xl mb-4 mx-auto opacity-70">📝</div>
                        <p class="text-lg font-bold mb-2">В этом уроке пока нет упражнений</p>
                        <p class="text-sm">Нажмите кнопку добавления справа, чтобы создать первое задание.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="w-full lg:w-80 flex-shrink-0">
            <div class="sticky top-8 space-y-6">
                
                @if($lesson->tasks->where('is_homework', false)->count() > 0)
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
                        <div class="space-y-2 max-h-[400px] overflow-y-auto custom-scrollbar">
                            @foreach($lesson->tasks->where('is_homework', false)->values() as $index => $rTask)
                                <a href="{{ route('admin.edit', $rTask->id) }}" class="flex items-center justify-between p-3 bg-slate-50 border border-slate-100 rounded-xl hover:border-blue-400 hover:bg-white hover:shadow-md transition-all group">
                                    <div class="flex items-center gap-3 overflow-hidden">
                                        <span class="w-6 h-6 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-[10px] font-black text-slate-400 group-hover:text-blue-600 transition-colors flex-shrink-0">{{ $index + 1 }}</span>
                                        <span class="text-xs font-bold text-slate-600 truncate group-hover:text-blue-700 transition-colors" title="{{ $rTask->title }}">{{ $rTask->title }}</span>
                                    </div>
                                    <span class="text-slate-300 group-hover:text-blue-500 opacity-0 group-hover:opacity-100 transition-opacity ml-2">✎</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200 text-center flex flex-col items-center justify-center">
                    <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center text-3xl mb-4">🧩</div>
                    <h3 class="font-black text-slate-800 text-lg mb-2">Новое упражнение</h3>
                    <p class="text-xs text-slate-500 mb-6">Создайте задание с нуля, выбрав нужный шаблон в конструкторе.</p>
                    
                    <a href="{{ route('admin.create', ['type' => 'blank', 'lesson_id' => $lesson->id]) }}" class="w-full bg-slate-800 hover:bg-blue-600 text-white py-4 rounded-2xl font-black uppercase text-xs tracking-widest transition-all shadow-lg shadow-slate-200">
                        + Добавить
                    </a>
                </div>

            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    function editLessonTitleTop() {
        const display = document.getElementById('lesson-title-display');
        const oldTitle = display.innerText;
        
        const input = document.createElement('input');
        input.value = oldTitle;
        input.className = "text-3xl font-black text-slate-800 border-b-2 border-blue-500 outline-none bg-transparent w-auto py-1";
        
        display.parentNode.replaceChild(input, display);
        input.focus();

        const save = async () => {
            const newTitle = input.value.trim();
            if (newTitle && newTitle !== oldTitle) {
                try {
                    await fetch(`/admin/lessons/{{ $lesson->id }}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ title: newTitle })
                    });
                    display.innerText = newTitle;
                } catch (e) {
                    display.innerText = oldTitle;
                    alert('Ошибка при сохранении названия');
                }
            } else {
                display.innerText = oldTitle;
            }
            if (input.parentNode) input.parentNode.replaceChild(display, input);
        };

        input.onblur = save;
        input.onkeydown = (e) => { if(e.key === 'Enter') { e.preventDefault(); save(); } };
    }

    async function saveLessonSettings() {
        const h = document.getElementById('duration_h').value;
        const m = document.getElementById('duration_m').value;
        const strict = document.querySelector('input[name="is_strict_order"]:checked').value;
        
        const response = await fetch(`/admin/lessons/{{ $lesson->id }}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                duration_minutes: parseInt(h) * 60 + parseInt(m),
                is_strict_order: strict
            })
        });
        
        if(response.ok) {
            const indicator = document.getElementById('save-indicator');
            indicator.classList.remove('opacity-0');
            setTimeout(() => indicator.classList.add('opacity-0'), 2000);
        }
    }

    const taskList = document.getElementById('tasks-in-lesson');
    if (taskList) {
        Sortable.create(taskList, {
            animation: 150, handle: '.handle', ghostClass: 'opacity-40', dragClass: 'shadow-2xl',
            onEnd: async function() {
                const items = Array.from(taskList.querySelectorAll('.task-card'));
                const ids = items.map(el => el.dataset.id);
                
                items.forEach((item, idx) => {
                    const titleEl = item.querySelector('.task-title-display');
                    const splitText = titleEl.innerText.split('"');
                    if(splitText.length > 1) {
                        titleEl.innerHTML = `Упражнение: №${idx + 1} <span class="text-blue-600 ml-2">"${splitText[1]}"</span>`;
                    }
                });

                await fetch("{{ route('admin.tasks.reorder') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ ids: ids })
                });
                
                setTimeout(() => window.location.reload(), 300);
            }
        });
    }
</script>
@endsection