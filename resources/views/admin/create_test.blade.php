@extends('layouts.app')
@section('header_title', isset($task) ? 'Редактировать Мультитест' : 'Создание Мультитеста')

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
            <h1 class="text-2xl font-black text-slate-800">Создание мультитеста (в общий банк)</h1>
        @endif
    </div>

    <div class="flex flex-col lg:flex-row gap-10">
        
        <div class="flex-1">
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200">

                <form action="{{ isset($task) ? route('admin.update', $task->id) : route('admin.store') }}" method="POST" id="task-form" onsubmit="return validateForm(event)">
                    @csrf 
                    @if(isset($task)) @method('PUT') @endif
                    
                    <input type="hidden" name="type" value="test">
                    
                    @if(isset($lesson))
                        <input type="hidden" name="lesson_id" value="{{ $lesson->id }}">
                        <input type="hidden" name="is_homework" value="{{ request('is_homework', isset($task) ? $task->is_homework : 0) }}">
                    @endif
                    
                    <div class="mb-6 border-b border-slate-100 pb-6">
                        <h2 class="text-xs font-black text-slate-400 mb-2 uppercase tracking-wide">Название теста:</h2>
                        <input type="text" name="title" value="{{ old('title', $task->title ?? '') }}" onblur="triggerAutoSave()" class="w-full border-none p-0 focus:ring-0 outline-none font-black text-2xl text-slate-800 placeholder-slate-200" placeholder="Например: Итоговый тест по лексике..." required>
                    </div>

                    <div class="mb-8 bg-slate-50 p-6 rounded-2xl border border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" id="has-points" class="w-5 h-5 text-blue-600 rounded border-slate-300 focus:ring-blue-500" onchange="togglePointsInput(); triggerAutoSave();" {{ (isset($task) && $task->points > 0) ? 'checked' : '' }}>
                                <span class="font-bold text-slate-700 group-hover:text-blue-700 transition-colors">Оценивается в баллах (Контрольная)</span>
                            </label>
                            <p class="text-xs text-slate-400 ml-8 mt-1">Если включено, ученик не увидит правильные ответы после сдачи.</p>
                        </div>
                        
                        <div class="w-full sm:w-32 {{ (isset($task) && $task->points > 0) ? '' : 'hidden' }} transition-all" id="points-container">
                            <input type="number" name="points" id="points-input" value="{{ old('points', $task->points ?? 0) }}" min="0" onblur="triggerAutoSave()" class="w-full text-center text-xl font-black border border-slate-200 p-3 rounded-xl outline-none focus:border-blue-500 bg-white">
                        </div>
                    </div>

                    <div class="mb-10 border-b border-slate-100 pb-8">
                        <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-1">Текст перед вопросами (Опционально)</h2>
                        <p class="text-xs text-slate-500 mb-4">Напишите здесь текст для чтения или общее правило.</p>
                        
                        <div class="border border-slate-200 rounded-2xl overflow-hidden focus-within:border-blue-500 transition-all bg-white shadow-sm">
                            <div class="bg-slate-50/80 border-b border-slate-200 px-3 py-2 flex items-center gap-1 flex-wrap">
                                <button type="button" onclick="format('bold')" class="p-2 text-slate-500 hover:bg-slate-200 hover:text-slate-900 rounded-lg transition-colors font-bold" title="Жирный">B</button>
                                <button type="button" onclick="format('italic')" class="p-2 text-slate-500 hover:bg-slate-200 hover:text-slate-900 rounded-lg transition-colors italic font-serif" title="Курсив">I</button>
                                <button type="button" onclick="format('underline')" class="p-2 text-slate-500 hover:bg-slate-200 hover:text-slate-900 rounded-lg transition-colors underline" title="Подчеркнутый">U</button>
                                <div class="w-px h-6 bg-slate-300 mx-2"></div>
                                <button type="button" onclick="format('insertOrderedList')" class="p-2 text-slate-500 hover:bg-slate-200 hover:text-slate-900 rounded-lg transition-colors" title="Нумерованный список">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="10" y1="6" x2="21" y2="6"></line><line x1="10" y1="12" x2="21" y2="12"></line><line x1="10" y1="18" x2="21" y2="18"></line><path d="M4 6h1v4"></path><path d="M4 10h2"></path><path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"></path></svg>
                                </button>
                                <button type="button" onclick="format('insertUnorderedList')" class="p-2 text-slate-500 hover:bg-slate-200 hover:text-slate-900 rounded-lg transition-colors" title="Маркированный список">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                                </button>
                            </div>
                            <input type="hidden" name="content" id="hidden-content">
                            <div id="custom-editor" contenteditable="true" onblur="triggerAutoSave()" class="p-6 min-h-[150px] outline-none text-lg text-slate-700 leading-relaxed bg-white" placeholder="Если нужно, вставьте сюда текст...">{!! isset($task) ? $task->content : '' !!}</div>
                        </div>
                    </div>

                    <div id="error-message" class="hidden mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center gap-3">
                        <span class="font-bold text-xl">!</span>
                        <span class="font-medium text-sm" id="error-text"></span>
                    </div>

                    <div id="questions-container" class="space-y-8 mb-8">
                        @if(isset($task) && is_array($task->options))
                            @foreach($task->options as $qIndex => $question)
                                <div class="question-block bg-slate-50 border border-slate-200 rounded-2xl p-6 relative shadow-sm transition-all focus-within:border-blue-300 focus-within:ring-4 focus-within:ring-blue-50">
                                    <button type="button" onclick="this.closest('.question-block').remove(); triggerAutoSave();" class="absolute -top-3 -right-3 bg-white border border-slate-200 text-slate-300 hover:text-red-500 hover:border-red-200 w-8 h-8 rounded-full flex items-center justify-center shadow-sm transition-all opacity-0 group-hover:opacity-100 z-10" title="Удалить вопрос">✕</button>
                                    
                                    <div class="mb-6 pr-4">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Вопрос {{ $qIndex + 1 }}</label>
                                        <input type="text" name="questions[{{ $qIndex }}][text]" value="{{ $question['text'] ?? '' }}" onblur="triggerAutoSave()" class="w-full border border-slate-200 p-4 rounded-xl focus:border-blue-500 outline-none font-bold text-slate-800 bg-white shadow-sm" placeholder="Текст вопроса..." required>
                                    </div>
                                    
                                    <div class="mb-6 bg-white p-4 rounded-xl border border-slate-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Тип ответа:</label>
                                        <select name="questions[{{ $qIndex }}][type]" class="border border-slate-200 p-2.5 rounded-lg bg-slate-50 outline-none focus:border-blue-500 font-bold text-slate-600 text-sm shadow-sm w-full sm:w-64" onchange="toggleInputTypes(this, {{ $qIndex }}); triggerAutoSave();">
                                            <option value="radio" {{ ($question['type'] ?? 'radio') === 'radio' ? 'selected' : '' }}>Один вариант (Кружочки)</option>
                                            <option value="checkbox" {{ ($question['type'] ?? '') === 'checkbox' ? 'selected' : '' }}>Несколько (Галочки)</option>
                                        </select>
                                    </div>
                                    
                                    <div class="answers-container space-y-3 mb-4" id="answers-{{ $qIndex }}">
                                        @foreach($question['answers'] ?? [] as $aIndex => $answer)
                                            <div class="flex items-center gap-4 bg-white p-3 rounded-xl border border-slate-200 shadow-sm transition-all focus-within:border-blue-400">
                                                <input type="hidden" name="questions[{{ $qIndex }}][answers][{{ $aIndex }}][is_correct]" value="0">
                                                <label class="relative flex items-center justify-center cursor-pointer ml-1">
                                                    <input type="{{ $question['type'] ?? 'radio' }}" name="{{ ($question['type'] ?? 'radio') === 'radio' ? 'radio_group_'.$qIndex : 'questions['.$qIndex.'][answers]['.$aIndex.'][is_correct]' }}" class="peer sr-only answer-marker" {{ ($answer['is_correct'] ?? '0') === '1' ? 'checked' : '' }} onchange="setCorrect(this); triggerAutoSave();">
                                                    <div class="custom-control-visual w-6 h-6 {{ ($question['type'] ?? 'radio') === 'radio' ? 'rounded-full' : 'rounded-md' }} border-2 border-slate-300 peer-checked:border-blue-600 peer-checked:bg-blue-600 transition-colors shadow-inner flex items-center justify-center">
                                                        <svg class="w-4 h-4 text-white opacity-0 peer-checked:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                                    </div>
                                                </label>
                                                <input type="text" name="questions[{{ $qIndex }}][answers][{{ $aIndex }}][text]" value="{{ $answer['text'] ?? '' }}" onblur="triggerAutoSave()" placeholder="Вариант ответа..." class="flex-1 border-none bg-transparent focus:ring-0 outline-none font-medium text-slate-700" required>
                                                <button type="button" onclick="this.parentElement.remove(); triggerAutoSave();" class="text-slate-300 hover:text-red-500 px-2 transition-colors">✕</button>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="button" onclick="addAnswer({{ $qIndex }})" class="text-xs font-bold text-blue-500 hover:text-blue-700 transition-colors uppercase tracking-wider block mt-4">
                                        + Добавить вариант ответа
                                    </button>
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
                        <button type="submit" onclick="document.getElementById('hidden-content').value = editor.innerHTML;" class="bg-blue-600 text-white px-10 py-4 rounded-2xl font-black uppercase tracking-widest hover:bg-blue-700 shadow-lg shadow-blue-100 transition-all">
                            {{ isset($task) ? 'Обновить тест' : 'Сохранить и добавить в урок' }}
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
                        <span class="text-xs font-black uppercase tracking-tighter leading-tight text-blue-600">{{ $currentWidget['name'] ?? 'Мультитест' }}</span>
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

    const editor = document.getElementById('custom-editor');
    function format(command, value = null) { document.execCommand(command, false, value); editor.focus(); triggerAutoSave(); }
    editor.addEventListener('paste', function(e) {
        e.preventDefault();
        const text = (e.originalEvent || e).clipboardData.getData('text/plain');
        document.execCommand('insertText', false, text);
        triggerAutoSave();
    });

    let qIndex = {{ isset($task) && is_array($task->options) ? count($task->options) : 0 }};

    function addQuestion() {
        const container = document.getElementById('questions-container');
        const questionHtml = `
            <div class="question-block bg-slate-50 border border-slate-200 rounded-2xl p-6 relative shadow-sm transition-all focus-within:border-blue-300 focus-within:ring-4 focus-within:ring-blue-50">
                <button type="button" onclick="this.closest('.question-block').remove(); triggerAutoSave();" class="absolute -top-3 -right-3 bg-white border border-slate-200 text-slate-300 hover:text-red-500 hover:border-red-200 w-8 h-8 rounded-full flex items-center justify-center shadow-sm transition-all opacity-0 group-hover:opacity-100 z-10" title="Удалить вопрос">✕</button>
                
                <div class="mb-6 pr-4">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Вопрос ${qIndex + 1}</label>
                    <input type="text" name="questions[${qIndex}][text]" onblur="triggerAutoSave()" class="w-full border border-slate-200 p-4 rounded-xl focus:border-blue-500 outline-none font-bold text-slate-800 bg-white shadow-sm" placeholder="Текст вопроса..." required>
                </div>
                
                <div class="mb-6 bg-white p-4 rounded-xl border border-slate-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Тип ответа:</label>
                    <select name="questions[${qIndex}][type]" class="border border-slate-200 p-2.5 rounded-lg bg-slate-50 outline-none focus:border-blue-500 font-bold text-slate-600 text-sm shadow-sm w-full sm:w-64" onchange="toggleInputTypes(this, ${qIndex}); triggerAutoSave();">
                        <option value="radio">Один вариант (Кружочки)</option>
                        <option value="checkbox">Несколько (Галочки)</option>
                    </select>
                </div>
                
                <div class="answers-container space-y-3 mb-4" id="answers-${qIndex}"></div>
                <button type="button" onclick="addAnswer(${qIndex})" class="text-xs font-bold text-blue-500 hover:text-blue-700 transition-colors uppercase tracking-wider block mt-4">
                    + Добавить вариант ответа
                </button>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', questionHtml);
        addAnswer(qIndex); addAnswer(qIndex);
        qIndex++;
    }

    function addAnswer(questionId) {
        const container = document.getElementById(`answers-${questionId}`);
        const answerCount = container.children.length;
        const selectElement = container.closest('.question-block').querySelector('select');
        const inputType = selectElement.value; 
        const isRadio = inputType === 'radio';
        
        const answerHtml = `
            <div class="flex items-center gap-4 bg-white p-3 rounded-xl border border-slate-200 shadow-sm transition-all focus-within:border-blue-400">
                <input type="hidden" name="questions[${questionId}][answers][${answerCount}][is_correct]" value="0">
                <label class="relative flex items-center justify-center cursor-pointer ml-1">
                    <input type="${inputType}" name="${isRadio ? 'radio_group_'+questionId : 'questions['+questionId+'][answers]['+answerCount+'][is_correct]'}" class="peer sr-only answer-marker" onchange="setCorrect(this); triggerAutoSave();">
                    <div class="custom-control-visual w-6 h-6 ${isRadio ? 'rounded-full' : 'rounded-md'} border-2 border-slate-300 peer-checked:border-blue-600 peer-checked:bg-blue-600 transition-colors shadow-inner flex items-center justify-center">
                        <svg class="w-4 h-4 text-white opacity-0 peer-checked:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                </label>
                <input type="text" name="questions[${questionId}][answers][${answerCount}][text]" onblur="triggerAutoSave()" placeholder="Вариант ответа..." class="flex-1 border-none bg-transparent focus:ring-0 outline-none font-medium text-slate-700" required>
                <button type="button" onclick="this.parentElement.remove(); triggerAutoSave();" class="text-slate-300 hover:text-red-500 px-2 transition-colors">✕</button>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', answerHtml);
    }

    function toggleInputTypes(selectElement, questionId) {
        const type = selectElement.value;
        const container = document.getElementById(`answers-${questionId}`);
        const markers = container.querySelectorAll('.answer-marker');
        const visuals = container.querySelectorAll('.custom-control-visual');

        markers.forEach((marker, index) => {
            marker.type = type;
            if(type === 'radio') {
                marker.name = `radio_group_${questionId}`;
                visuals[index].classList.add('rounded-full');
                visuals[index].classList.remove('rounded-md');
                // Сбрасываем все скрытые инпуты при переключении на радио
                container.querySelectorAll('input[type="hidden"][name$="[is_correct]"]').forEach(i => i.value = '0');
                marker.checked = false;
            } else {
                marker.name = marker.previousElementSibling.name; 
                visuals[index].classList.add('rounded-md');
                visuals[index].classList.remove('rounded-full');
            }
        });
    }

    // ИСПРАВЛЕННАЯ ЛОГИКА: корректно работает и для радио, и для чекбоксов
    function setCorrect(input) {
        const container = input.closest('.answers-container');
        if (input.type === 'radio') {
            container.querySelectorAll('input[type="hidden"][name$="[is_correct]"]').forEach(i => i.value = '0');
            input.parentElement.previousElementSibling.value = '1';
        } else {
            input.parentElement.previousElementSibling.value = input.checked ? '1' : '0';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        @if(!isset($task) || empty($task->options))
            addQuestion();
        @endif
    });

    function validateForm(e) {
        document.getElementById('hidden-content').value = editor.innerHTML;
        const errorBox = document.getElementById('error-message');
        const questions = document.querySelectorAll('.question-block');
        
        if (questions.length === 0) {
            e.preventDefault(); showError("Добавьте хотя бы один вопрос в тест."); return false;
        }

        let isValid = true;
        questions.forEach((q, index) => {
            const markers = q.querySelectorAll('.answer-marker');
            if (markers.length < 2) {
                isValid = false; showError(`В вопросе ${index + 1} должно быть минимум 2 варианта ответа.`);
            }
            let hasCorrect = false;
            markers.forEach(m => { if (m.checked) hasCorrect = true; });
            if (!hasCorrect) {
                isValid = false; showError(`В вопросе ${index + 1} не выбран правильный вариант ответа.`);
            }
        });

        if (!isValid) { e.preventDefault(); return false; }

        // Перед отправкой (без AJAX) нужно вернуть правильные имена радиокнопкам, чтобы сервер их понял
        document.querySelectorAll('input[type="radio"].answer-marker').forEach(radio => {
            const hiddenInput = radio.parentElement.previousElementSibling;
            radio.name = hiddenInput.name;
        });
        
        return true;
    }

    function showError(message) {
        const errorBox = document.getElementById('error-message');
        document.getElementById('error-text').innerText = message;
        errorBox.classList.remove('hidden');
        errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // ЛОГИКА АВТОСОХРАНЕНИЯ 
    async function triggerAutoSave() {
        @if(isset($task))
            document.getElementById('hidden-content').value = editor.innerHTML;
            const form = document.getElementById('task-form');
            
            // Клонируем форму, чтобы временно поменять имена радио-кнопок для отправки
            const formData = new FormData(form);
            
            document.querySelectorAll('input[type="radio"].answer-marker').forEach(radio => {
                if(radio.checked) {
                    const hiddenInputName = radio.parentElement.previousElementSibling.name;
                    formData.set(hiddenInputName, '1'); // Подменяем значение для сервера
                }
            });
            
            try {
                const res = await fetch("{{ route('admin.tasks.autosave', $task->id) }}", {
                    method: 'POST', // Используем POST (Laravel понимает _method=PUT внутри FormData)
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