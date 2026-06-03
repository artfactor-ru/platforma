@extends('layouts.app')
@section('header_title', 'Редактирование курса')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<form id="delete-course-form" action="{{ route('admin.courses.destroy', $course->id) }}" method="POST" class="hidden" onsubmit="return confirm('Вы уверены, что хотите навсегда удалить этот курс?');">
    @csrf @method('DELETE')
</form>

<div class="max-w-5xl mx-auto pb-20">
    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
        <a href="{{ route('admin.courses.index') }}" class="hover:text-blue-600 font-medium">Библиотека курсов</a>
        <span>/</span>
        <span class="text-slate-900 font-bold">{{ $course->title }}</span>
    </div>

    <form action="{{ route('admin.courses.update', $course->id) }}" method="POST" id="course-form">
        @csrf @method('PUT')
        
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200 mb-8 relative">
            
            <div id="save-indicator" class="absolute top-6 right-8 text-green-500 font-bold text-sm opacity-0 transition-opacity duration-300 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Сохранено
            </div>

            <div class="flex flex-col gap-4">
                <div class="group relative">
                    <input type="text" name="title" value="{{ $course->title }}" 
                           onblur="autoSaveCourseField('title', this.value)"
                           class="text-3xl font-black text-slate-800 border-none p-0 focus:ring-0 w-full bg-transparent"
                           placeholder="Название курса...">
                    <div class="absolute -left-6 top-1/2 -translate-y-1/2 text-slate-200 opacity-0 group-hover:opacity-100 transition-opacity">✎</div>
                </div>
                
                <div class="flex flex-wrap items-center gap-6">
                    <div class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-100">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Предмет</span>
                        <input type="text" name="subject" value="{{ $course->subject }}" 
                               onblur="autoSaveCourseField('subject', this.value)"
                               class="border-none focus:ring-0 p-0 text-sm font-bold text-slate-700 bg-transparent w-40" 
                               placeholder="Напр: Математика">
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Статус:</span>
                        <span class="px-3 py-1 rounded-lg text-xs font-black uppercase {{ $course->status === 'published' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ $course->status === 'published' ? 'Готов' : 'Черновик' }}
                        </span>
                    </div>
                </div>

                <div class="mt-2">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Краткое описание курса:</span>
                    <textarea name="description" rows="2" 
                              onblur="autoSaveCourseField('description', this.value)"
                              class="w-full bg-slate-50 border border-slate-100 rounded-xl p-3 focus:border-blue-300 focus:bg-white focus:ring-0 transition-all text-sm text-slate-700 outline-none resize-none" 
                              placeholder="О чем этот курс? Чему научится ученик?">{{ $course->description }}</textarea>
                </div>
            </div>
        </div>

        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-xl font-black text-slate-800 uppercase tracking-tighter flex items-center gap-3">
                Содержание курса 
                <span id="lessons-count-badge" class="w-6 h-6 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-xs font-bold">{{ $course->lessons->count() }}</span>
            </h2>
        </div>

        <div id="lessons-list" class="space-y-3 mb-3">
            @foreach($course->lessons as $index => $lesson)
                <div class="lesson-item bg-white border border-slate-200 rounded-2xl p-4 flex items-center gap-4 hover:shadow-lg hover:border-blue-200 transition-all group" data-id="{{ $lesson->id }}">
                    {{-- Кнопка перетаскивания --}}
                    <div class="handle cursor-grab text-slate-300 hover:text-slate-500 px-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path></svg>
                    </div>

                    {{-- Порядковый номер --}}
                    <div class="lesson-number w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-sm font-black text-slate-400">
                        {{ $index + 1 }}
                    </div>

                    <div class="flex-1 flex items-center justify-between">
                        {{-- ЛЕВАЯ ЧАСТЬ: Название урока --}}
                        <div class="flex items-center gap-3">
                            <span onclick="editLessonTitle(this, {{ $lesson->id }})" class="font-bold text-slate-700 cursor-text py-1 px-2 rounded hover:bg-slate-50 transition-colors">{{ $lesson->title }}</span>
                        </div>

                        {{-- ПРАВАЯ ЧАСТЬ: Статусы и Действия --}}
                        <div class="flex items-center gap-4">
                            
                            {{-- Блок Домашнего задания --}}
                            @if($lesson->tasks->where('is_homework', true)->count() > 0)
                                <a href="{{ route('admin.lessons.homework', $lesson->id) }}" class="bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-600 px-3 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest transition-all flex items-center gap-1.5 shadow-sm">
                                    Домашнее задание 
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                </a>
                            @endif
                            
                            {{-- Блок Контрольной работы (Расшифровано) --}}
                            @if($lesson->tasks->where('points', '>', 0)->count() > 0)
                                <div class="flex items-center gap-1.5 px-3 py-1.5 bg-purple-50 text-purple-600 rounded-lg border border-purple-100 shadow-sm" title="В этом уроке есть оцениваемые задания">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                    <span class="text-[9px] font-black uppercase tracking-widest">Контрольная работа</span>
                                </div>
                            @endif

                            {{-- Разделитель --}}
                            <div class="w-px h-6 bg-slate-100 mx-1"></div>

                            {{-- Кнопки управления --}}
                            <div class="flex items-center gap-1">
                                <a href="{{ route('admin.lessons.builder', $lesson->id) }}" class="p-2 text-slate-400 hover:text-blue-600 transition-colors" title="Настроить контент">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </a>
                                <button type="button" onclick="deleteLesson({{ $lesson->id }}, this)" class="p-2 text-slate-300 hover:text-red-500 transition-colors" title="Удалить урок">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="lesson-quick-add bg-slate-50/50 border-2 border-dashed border-slate-200 rounded-2xl p-4 flex items-center gap-4 transition-all focus-within:border-blue-400 focus-within:bg-white focus-within:shadow-lg">
            <div class="text-slate-200 px-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            </div>

            <div id="quick-lesson-number" class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-sm font-black text-slate-300">
                {{ $course->lessons->count() + 1 }}
            </div>

            <div class="flex-1 flex items-center gap-4">
                <input type="text" id="quick-lesson-title" 
                       class="flex-1 bg-transparent border-none focus:ring-0 font-bold text-slate-700 placeholder-slate-300" 
                       placeholder="Введите название нового урока...">
                
                <button type="button" onclick="submitQuickLesson()" 
                        class="bg-white hover:bg-blue-600 hover:text-white text-slate-400 border border-slate-200 px-6 py-2 rounded-xl font-black uppercase text-[10px] tracking-widest transition-all shadow-sm">
                    Сохранить
                </button>
            </div>
        </div>

        <div class="fixed bottom-0 left-0 right-0 bg-white/90 backdrop-blur-md border-t border-slate-200 p-4 z-50">
            <div class="max-w-5xl mx-auto flex items-center justify-between">
                <button type="submit" form="delete-course-form" class="text-red-500 font-bold text-sm hover:underline">Удалить курс</button>
                
                <div class="flex items-center gap-4">
                    <input type="hidden" name="status" id="status-input" value="{{ $course->status }}">
                    <button type="submit" onclick="setStatus('draft')" class="px-6 py-3 rounded-2xl border border-slate-300 font-bold text-slate-600 hover:bg-white transition-all shadow-sm">Сохранить как черновик</button>
                    <button type="submit" onclick="setStatus('published')" class="px-6 py-3 rounded-2xl bg-blue-600 text-white font-bold hover:bg-blue-700 shadow-xl shadow-blue-200 transition-all">Опубликовать готовый курс</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    // МАГИЯ АВТОСОХРАНЕНИЯ ПОЛЕЙ КУРСА
    async function autoSaveCourseField(field, value) {
        try {
            const response = await fetch("{{ route('admin.courses.update', $course->id) }}", {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ [field]: value })
            });

            if (response.ok) {
                const indicator = document.getElementById('save-indicator');
                indicator.classList.remove('opacity-0');
                setTimeout(() => indicator.classList.add('opacity-0'), 2000);
            }
        } catch (e) {
            console.error('Ошибка сохранения', e);
        }
    }

    // УДАЛЕНИЕ УРОКА (AJAX)
    async function deleteLesson(lessonId, buttonElement) {
        if (!confirm('Вы уверены, что хотите удалить этот урок?')) return;

        try {
            const response = await fetch(`/admin/lessons/${lessonId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                // Плавно удаляем элемент из верстки
                const item = buttonElement.closest('.lesson-item');
                item.remove();
                
                // Пересчитываем нумерацию оставшихся уроков
                const lessons = document.querySelectorAll('.lesson-item');
                lessons.forEach((el, idx) => {
                    el.querySelector('.lesson-number').innerText = idx + 1;
                });
                
                // Обновляем счетчики
                document.getElementById('lessons-count-badge').innerText = lessons.length;
                document.getElementById('quick-lesson-number').innerText = lessons.length + 1;
            }
        } catch (e) { alert('Ошибка при удалении урока'); }
    }

    // БЫСТРОЕ ДОБАВЛЕНИЕ УРОКА
    async function submitQuickLesson() {
        const titleInput = document.getElementById('quick-lesson-title');
        const title = titleInput.value.trim();
        
        if (!title) return titleInput.focus();

        try {
            const response = await fetch("{{ route('admin.courses.addLesson', $course->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ title: title })
            });

            if (response.ok) window.location.reload();
        } catch (e) { alert('Ошибка при добавлении урока'); }
    }

    document.getElementById('quick-lesson-title').onkeydown = (e) => {
        if(e.key === 'Enter') { e.preventDefault(); submitQuickLesson(); }
    };

    // ИНЛАЙН РЕДАКТИРОВАНИЕ СУЩЕСТВУЮЩИХ НАЗВАНИЙ УРОКОВ
    function editLessonTitle(element, lessonId) {
        if (element.tagName === 'INPUT') return;
        const oldTitle = element.innerText;
        const input = document.createElement('input');
        input.value = oldTitle;
        input.className = "font-bold text-slate-700 border-b-2 border-blue-500 outline-none p-1 bg-blue-50 rounded w-64";
        element.parentNode.replaceChild(input, element);
        input.focus();
        
        const save = async () => {
            const newTitle = input.value.trim();
            if (newTitle && newTitle !== oldTitle) {
                const response = await fetch(`/admin/lessons/${lessonId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ title: newTitle })
                });
                if (response.ok) element.innerText = newTitle;
            } else {
                element.innerText = oldTitle;
            }
            if (input.parentNode) input.parentNode.replaceChild(element, input);
        };
        input.onblur = save;
        input.onkeydown = (e) => { if(e.key === 'Enter') { e.preventDefault(); save(); } };
    }

    // СОРТИРОВКА
    const el = document.getElementById('lessons-list');
    if (el) {
        Sortable.create(el, {
            animation: 150, handle: '.handle', ghostClass: 'bg-blue-50',
            onEnd: async function () {
                const items = Array.from(el.querySelectorAll('.lesson-item'));
                const ids = items.map(item => item.dataset.id);
                
                // Визуальный пересчет номеров
                items.forEach((item, idx) => {
                    item.querySelector('.lesson-number').innerText = idx + 1;
                });

                // Отправка в БД
                await fetch("{{ route('admin.lessons.reorder') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ ids: ids })
                });
            }
        });
    }

    function setStatus(s) { document.getElementById('status-input').value = s; }
</script>
@endsection