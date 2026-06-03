@extends('layouts.app')
@section('header_title', isset($task) ? 'Редактировать Аудио/Видео тест' : 'Создание: Аудио/Видео тест')

@section('content')
<style>
    @keyframes pulse-ring {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 15px rgba(239, 68, 68, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }
    .recording-pulse { animation: pulse-ring 2s infinite; }
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
            <h1 class="text-2xl font-black text-slate-800">Создание медиа-теста (в общий банк)</h1>
        @endif
    </div>

    <div class="flex flex-col lg:flex-row gap-10">
        
        <div class="flex-1">
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200">

                <form action="{{ isset($task) ? route('admin.update', $task->id) : route('admin.store') }}" method="POST" enctype="multipart/form-data" id="task-form" onsubmit="return validateForm(event)">
                    @csrf 
                    @if(isset($task)) @method('PUT') @endif
                    
                    <input type="hidden" name="type" value="media_test">
                    
                    @if(isset($lesson))
                        <input type="hidden" name="lesson_id" value="{{ $lesson->id }}">
                        <input type="hidden" name="is_homework" value="{{ request('is_homework', isset($task) ? $task->is_homework : 0) }}">
                    @endif
                    
                    <div class="mb-6 border-b border-slate-100 pb-6">
                        <h2 class="text-xs font-black text-slate-400 mb-2 uppercase tracking-wide">Заголовок упражнения:</h2>
                        <input type="text" name="title" value="{{ old('title', $task->title ?? '') }}" onblur="triggerAutoSave()" class="w-full border-none p-0 focus:ring-0 outline-none font-black text-2xl text-slate-800 placeholder-slate-200" placeholder="Например: Аудирование. Текст про весну..." required>
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
                                <input type="number" name="points" id="points-input" value="{{ old('points', $task->points ?? 0) }}" min="0" onblur="triggerAutoSave()" class="w-full text-center text-xl font-black border border-slate-200 p-2.5 rounded-xl outline-none focus:border-blue-500 bg-white">
                            </div>
                        </div>

                        <div class="flex-1 bg-purple-50/50 p-6 rounded-2xl border border-purple-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div>
                                <span class="font-black text-slate-800 uppercase tracking-widest text-xs block mb-1">Ограничение прослушиваний</span>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">0 = Без ограничений</span>
                            </div>
                            <div class="w-full sm:w-28">
                                <input type="number" name="max_plays" value="{{ isset($task) ? ($task->options['max_plays'] ?? 0) : 0 }}" min="0" max="10" onblur="triggerAutoSave()" class="w-full text-center text-xl font-black border border-purple-200 text-purple-700 p-2.5 rounded-xl outline-none focus:ring-2 focus:ring-purple-200 focus:border-purple-400 bg-white shadow-sm" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-8 p-8 bg-slate-50 border border-slate-200 rounded-3xl shadow-sm relative overflow-hidden">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                            <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest">Медиа файл (Источник)</h2>
                            
                            <div class="flex bg-slate-200/70 p-1.5 rounded-xl overflow-x-auto w-full sm:w-auto">
                                <button type="button" id="tab-upload" class="px-5 py-2 text-xs font-black uppercase tracking-wider rounded-lg bg-white text-blue-600 shadow-sm transition-all whitespace-nowrap" onclick="switchMediaTab('upload')">📁 Загрузить</button>
                                <button type="button" id="tab-record" class="px-5 py-2 text-xs font-black uppercase tracking-wider rounded-lg text-slate-500 hover:text-slate-700 transition-all whitespace-nowrap" onclick="switchMediaTab('record')">🎙️ Голос</button>
                                <button type="button" id="tab-video" class="px-5 py-2 text-xs font-black uppercase tracking-wider rounded-lg text-slate-500 hover:text-slate-700 transition-all whitespace-nowrap" onclick="switchMediaTab('video')">📹 Камера</button>
                            </div>
                        </div>
                        
                        @if(isset($task) && !empty($task->options['media_path']))
                            <div class="mb-6 p-5 bg-blue-50 border border-blue-200 rounded-2xl flex items-center gap-4">
                                <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-2xl shadow-sm shrink-0">📀</div>
                                <div>
                                    <p class="text-sm font-black text-blue-800 mb-1">Файл уже сохранен в задании</p>
                                    <p class="text-xs font-medium text-blue-600">Если вы загрузите или запишете новый, старый будет заменен.</p>
                                </div>
                            </div>
                        @endif
                        
                        <div id="panel-upload" class="block">
                            <label class="block w-full cursor-pointer">
                                <div class="border-2 border-dashed border-slate-300 rounded-2xl p-8 text-center hover:border-blue-400 hover:bg-blue-50/50 transition-colors bg-white">
                                    <div class="text-4xl mb-3">📁</div>
                                    <p class="text-sm font-bold text-slate-700 mb-1">Нажмите для выбора файла</p>
                                    <p class="text-xs font-medium text-slate-400">Форматы: .mp3, .wav, .mp4, .mov</p>
                                    <input type="file" name="media_file" id="media-file-input" accept="audio/*,video/*" class="hidden" onchange="updateFileName(this); triggerAutoSave();" {{ isset($task) && !empty($task->options['media_path']) ? '' : 'required' }}>
                                </div>
                            </label>
                            <div id="file-name-display" class="mt-3 text-center text-xs font-bold text-blue-600 hidden"></div>
                        </div>

                        <div id="panel-record" class="hidden text-center py-8 bg-white border border-slate-200 rounded-2xl shadow-inner">
                            <div id="record-idle">
                                <button type="button" onclick="startAudioRecording()" class="w-20 h-20 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4 hover:bg-red-100 hover:scale-105 transition-all border border-red-100 shadow-sm">
                                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7 4a3 3 0 016 0v4a3 3 0 11-6 0V4zm4 10.93A7.001 7.001 0 0017 8a1 1 0 10-2 0A5 5 0 015 8a1 1 0 00-2 0 7.001 7.001 0 006 6.93V17H6a1 1 0 100 2h8a1 1 0 100-2h-3v-2.07z" clip-rule="evenodd"></path></svg>
                                </button>
                                <p class="font-black text-slate-700 uppercase tracking-widest text-xs">Записать голос с микрофона</p>
                            </div>

                            <div id="record-active" class="hidden">
                                <button type="button" onclick="stopAudioRecording()" class="w-20 h-20 bg-red-600 text-white rounded-full flex items-center justify-center mx-auto mb-4 recording-pulse transition-colors shadow-lg shadow-red-200">
                                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V5zm3 2a1 1 0 00-1 1v4a1 1 0 001 1h8a1 1 0 001-1V8a1 1 0 00-1-1H6z" clip-rule="evenodd"></path></svg>
                                </button>
                                <p class="font-black text-red-600 uppercase tracking-widest text-xs mb-2">Идёт запись...</p>
                                <p id="record-timer" class="text-2xl font-black text-slate-800 font-mono">00:00</p>
                            </div>

                            <div id="record-done" class="hidden px-8">
                                <div class="inline-flex items-center justify-center gap-2 bg-green-50 text-green-700 px-4 py-2 rounded-lg font-bold text-sm mb-6 border border-green-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Аудио успешно записано!
                                </div>
                                <audio id="audio-preview" controls class="w-full max-w-md mx-auto mb-6 outline-none bg-slate-50 rounded-full"></audio>
                                <button type="button" onclick="resetAudioRecording()" class="text-xs font-bold text-slate-400 hover:text-red-500 uppercase tracking-widest transition-colors flex items-center gap-2 mx-auto">
                                    <span>Удалить и перезаписать</span>
                                </button>
                            </div>
                        </div>

                        <div id="panel-video" class="hidden text-center py-6 bg-white border border-slate-200 rounded-2xl shadow-inner">
                            <div id="video-idle">
                                <div class="w-full max-w-lg mx-auto aspect-video bg-slate-900 rounded-2xl mb-6 flex items-center justify-center text-slate-500 flex-col shadow-inner">
                                    <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                    <span class="font-bold text-sm">Камера выключена</span>
                                </div>
                                <button type="button" onclick="initCamera()" class="bg-slate-100 text-slate-700 px-8 py-3 rounded-xl font-black uppercase tracking-widest text-xs hover:bg-slate-200 transition-all shadow-sm">Включить камеру</button>
                            </div>

                            <div id="video-preview-container" class="hidden relative w-full max-w-lg mx-auto aspect-video rounded-2xl overflow-hidden bg-black mb-6 shadow-lg border border-slate-800">
                                <video id="live-video" class="w-full h-full object-cover transform scale-x-[-1]" autoplay muted playsinline></video>
                                
                                <div id="video-recording-badge" class="hidden absolute top-4 left-4 bg-red-600/90 text-white text-xs font-bold px-4 py-2 rounded-full flex items-center gap-3 backdrop-blur-md shadow-lg">
                                    <div class="w-2.5 h-2.5 rounded-full bg-white animate-pulse"></div>
                                    <span id="video-timer" class="font-mono text-sm tracking-wider">00:00</span>
                                </div>
                            </div>

                            <div id="video-controls" class="hidden space-x-4">
                                <button type="button" id="btn-start-vid" onclick="startVideoRecording()" class="bg-red-50 border border-red-100 text-red-600 px-8 py-3 rounded-xl font-black uppercase tracking-widest text-xs hover:bg-red-100 transition-all flex items-center justify-center gap-3 mx-auto shadow-sm">
                                    <div class="w-3 h-3 rounded-full bg-red-600"></div> Начать запись
                                </button>
                                
                                <button type="button" id="btn-stop-vid" onclick="stopVideoRecording()" class="hidden bg-slate-900 text-white px-8 py-3 rounded-xl font-black uppercase tracking-widest text-xs hover:bg-black transition-all items-center justify-center gap-2 mx-auto shadow-lg">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V5zm3 2a1 1 0 00-1 1v4a1 1 0 001 1h8a1 1 0 001-1V8a1 1 0 00-1-1H6z" clip-rule="evenodd"></path></svg>
                                    Остановить
                                </button>
                            </div>

                            <div id="video-done" class="hidden flex flex-col items-center">
                                <video id="recorded-video" controls class="w-full max-w-lg mx-auto rounded-2xl bg-black mb-6 shadow-lg border border-slate-200"></video>
                                <div class="inline-flex items-center justify-center gap-2 bg-green-50 text-green-700 px-4 py-2 rounded-lg font-bold text-sm mb-4 border border-green-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Видео успешно сохранено!
                                </div>
                                <button type="button" onclick="resetVideoRecording()" class="text-xs font-bold text-slate-400 hover:text-red-500 uppercase tracking-widest transition-colors flex items-center gap-2 mx-auto">
                                    Удалить и снять заново
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mb-8 border-b border-slate-100 pb-8">
                        <h2 class="text-xs font-black text-slate-400 mb-2 uppercase tracking-wide">Описание / Текст (Опционально):</h2>
                        <input type="text" name="content" value="{{ old('content', $task->content ?? '') }}" onblur="triggerAutoSave()" class="w-full border border-slate-200 p-4 rounded-xl outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-50 bg-white font-medium text-slate-700" placeholder="Например: Посмотрите видео и выберите правильный ответ.">
                    </div>

                    <div class="mb-6 flex justify-between items-end px-2 border-b border-slate-100 pb-4">
                        <div>
                            <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-1">Вопросы к медиа</h2>
                            <p class="text-xs text-slate-500">Добавьте вопросы и отметьте правильные варианты.</p>
                        </div>
                    </div>

                    <div id="error-message" class="hidden mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center gap-3">
                        <span class="font-bold text-xl">!</span>
                        <span class="font-medium text-sm" id="error-text"></span>
                    </div>

                    <div id="questions-container" class="space-y-6 mb-8">
                        @if(isset($task) && isset($task->options['questions']))
                            @foreach($task->options['questions'] as $qIndex => $question)
                                <div class="question-block bg-white border border-slate-200 rounded-2xl p-6 relative shadow-sm focus-within:border-blue-300 focus-within:ring-4 focus-within:ring-blue-50 transition-all group">
                                    <button type="button" onclick="this.closest('.question-block').remove(); triggerAutoSave();" class="absolute -top-3 -right-3 bg-white border border-slate-200 text-slate-300 hover:text-red-500 hover:border-red-200 w-8 h-8 rounded-full flex items-center justify-center shadow-sm transition-all opacity-0 group-hover:opacity-100 z-10" title="Удалить вопрос">✕</button>
                                    
                                    <div class="mb-4 pr-4">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Вопрос</label>
                                        <input type="text" name="questions[{{ $qIndex }}][text]" value="{{ $question['text'] ?? '' }}" onblur="triggerAutoSave()" class="w-full border border-slate-200 p-3 rounded-xl outline-none focus:border-blue-500 font-bold text-slate-700 bg-slate-50 focus:bg-white transition-colors" required>
                                    </div>
                                    
                                    <div class="answers-container space-y-3 mb-4" id="answers-{{ $qIndex }}">
                                        @foreach($question['answers'] ?? [] as $aIndex => $answer)
                                            <div class="flex items-center gap-4 bg-slate-50 p-3 rounded-xl border border-slate-100 transition-all focus-within:bg-white focus-within:border-blue-200">
                                                <input type="hidden" name="questions[{{ $qIndex }}][answers][{{ $aIndex }}][is_correct]" value="0">
                                                <label class="relative flex items-center justify-center cursor-pointer ml-1">
                                                    <input type="radio" name="correct_q_{{ $qIndex }}" value="1" class="peer sr-only ans-marker" {{ ($answer['is_correct'] ?? '0') === '1' ? 'checked' : '' }} required onchange="setCorrect(this); triggerAutoSave();">
                                                    <div class="w-5 h-5 rounded-full border-2 border-slate-300 peer-checked:border-blue-600 peer-checked:bg-blue-600 transition-colors shadow-inner"></div>
                                                    <svg class="absolute w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                                </label>
                                                <input type="text" name="questions[{{ $qIndex }}][answers][{{ $aIndex }}][text]" value="{{ $answer['text'] ?? '' }}" onblur="triggerAutoSave()" class="flex-1 border-none bg-transparent focus:ring-0 outline-none font-medium text-slate-700" required>
                                                <button type="button" onclick="this.parentElement.remove(); triggerAutoSave();" class="text-slate-300 hover:text-red-500 px-2 transition-colors">✕</button>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="button" onclick="addAnswer({{ $qIndex }})" class="text-xs font-bold text-blue-500 hover:text-blue-700 transition-colors uppercase tracking-wider">+ Добавить вариант</button>
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
                        <div class="text-5xl mb-4">{{ $currentWidget['icon'] ?? '🎧' }}</div>
                        <span class="text-xs font-black uppercase tracking-tighter leading-tight text-blue-600">{{ $currentWidget['name'] ?? 'Аудио / Видео' }}</span>
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
    let currentStream = null; // Для выключения камеры при смене вкладок

    function togglePointsInput() {
        const cb = document.getElementById('has-points');
        const c = document.getElementById('points-container');
        const i = document.getElementById('points-input');
        if (cb.checked) { c.classList.remove('hidden'); if(i.value == 0) i.value = 10; } 
        else { c.classList.add('hidden'); i.value = 0; }
    }

    // ================== ЛОГИКА ТАБОВ ==================
    function switchMediaTab(tab) {
        document.getElementById('panel-upload').style.display = tab === 'upload' ? 'block' : 'none';
        document.getElementById('panel-record').style.display = tab === 'record' ? 'block' : 'none';
        document.getElementById('panel-video').style.display = tab === 'video' ? 'block' : 'none';
        
        ['upload', 'record', 'video'].forEach(t => {
            const btn = document.getElementById(`tab-${t}`);
            if (t === tab) {
                btn.className = "px-5 py-2 text-xs font-black uppercase tracking-wider rounded-lg bg-white text-blue-600 shadow-sm transition-all whitespace-nowrap";
            } else {
                btn.className = "px-5 py-2 text-xs font-black uppercase tracking-wider rounded-lg text-slate-500 hover:text-slate-700 transition-all whitespace-nowrap";
            }
        });

        // Требуем файл только если мы на вкладке Upload и еще ничего не загружено
        const fileInput = document.getElementById('media-file-input');
        const hasOldFile = {{ isset($task) && !empty($task->options['media_path']) ? 'true' : 'false' }};
        if (tab === 'upload') {
            if(fileInput.files.length === 0 && !hasOldFile) fileInput.required = true;
        } else {
            fileInput.required = false;
        }

        // Если ушли с вкладки "Видео" - выключаем лампочку вебки
        if (tab !== 'video' && currentStream) {
            currentStream.getTracks().forEach(track => track.stop());
            currentStream = null;
            resetVideoRecording(); // Сброс интерфейса видео
        }
    }

    function updateFileName(input) {
        const display = document.getElementById('file-name-display');
        if (input.files && input.files.length > 0) {
            display.innerText = "Выбран файл: " + input.files[0].name;
            display.classList.remove('hidden');
        } else {
            display.classList.add('hidden');
        }
    }

    // ================== ЗАПИСЬ АУДИО ==================
    let audioRecorder;
    let audioChunks = [];
    let audioTimer;
    let audioSecs = 0;

    async function startAudioRecording() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            audioRecorder = new MediaRecorder(stream);
            audioRecorder.ondataavailable = e => { if (e.data.size > 0) audioChunks.push(e.data); };
            
            audioRecorder.onstop = () => {
                clearInterval(audioTimer);
                stream.getTracks().forEach(track => track.stop()); // Гасим микрофон
                
                const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                document.getElementById('audio-preview').src = URL.createObjectURL(audioBlob);
                
                document.getElementById('record-active').classList.add('hidden');
                document.getElementById('record-done').classList.remove('hidden');

                const audioFile = new File([audioBlob], "voice_record_" + Date.now() + ".webm", { type: 'audio/webm' });
                const dt = new DataTransfer(); dt.items.add(audioFile);
                document.getElementById('media-file-input').files = dt.files;
                triggerAutoSave();
            };

            audioChunks = [];
            audioRecorder.start();
            document.getElementById('record-idle').classList.add('hidden');
            document.getElementById('record-active').classList.remove('hidden');
            
            audioSecs = 0;
            document.getElementById('record-timer').innerText = "00:00";
            audioTimer = setInterval(() => {
                audioSecs++;
                document.getElementById('record-timer').innerText = `${String(Math.floor(audioSecs / 60)).padStart(2, '0')}:${String(audioSecs % 60).padStart(2, '0')}`;
            }, 1000);
        } catch (err) { alert('Нет доступа к микрофону.'); }
    }

    function stopAudioRecording() { if (audioRecorder) audioRecorder.stop(); }
    
    function resetAudioRecording() {
        document.getElementById('record-done').classList.add('hidden');
        document.getElementById('record-idle').classList.remove('hidden');
        document.getElementById('media-file-input').value = ""; 
    }

    // ================== ЗАПИСЬ ВИДЕО ==================
    let videoRecorder;
    let videoChunks = [];
    let videoTimer;
    let videoSecs = 0;

    async function initCamera() {
        try {
            currentStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
            const liveVideo = document.getElementById('live-video');
            liveVideo.srcObject = currentStream;
            
            document.getElementById('video-idle').classList.add('hidden');
            document.getElementById('video-preview-container').classList.remove('hidden');
            document.getElementById('video-controls').classList.remove('hidden');
            document.getElementById('video-controls').classList.add('flex');
        } catch (err) { alert('Нет доступа к камере или микрофону.'); }
    }

    function startVideoRecording() {
        videoChunks = [];
        videoRecorder = new MediaRecorder(currentStream, { mimeType: 'video/webm' });
        
        videoRecorder.ondataavailable = e => { if (e.data.size > 0) videoChunks.push(e.data); };
        
        videoRecorder.onstop = () => {
            clearInterval(videoTimer);
            currentStream.getTracks().forEach(track => track.stop()); // Гасим камеру
            currentStream = null;

            const videoBlob = new Blob(videoChunks, { type: 'video/webm' });
            document.getElementById('recorded-video').src = URL.createObjectURL(videoBlob);
            
            document.getElementById('video-preview-container').classList.add('hidden');
            document.getElementById('video-controls').classList.remove('flex');
            document.getElementById('video-controls').classList.add('hidden');
            document.getElementById('video-done').classList.remove('hidden');

            const videoFile = new File([videoBlob], "webcam_record_" + Date.now() + ".webm", { type: 'video/webm' });
            const dt = new DataTransfer(); dt.items.add(videoFile);
            document.getElementById('media-file-input').files = dt.files;
            triggerAutoSave();
        };

        videoRecorder.start();
        
        document.getElementById('btn-start-vid').classList.add('hidden');
        document.getElementById('btn-stop-vid').classList.remove('hidden');
        document.getElementById('btn-stop-vid').classList.add('flex');
        document.getElementById('video-recording-badge').classList.remove('hidden');
        
        videoSecs = 0;
        document.getElementById('video-timer').innerText = "00:00";
        videoTimer = setInterval(() => {
            videoSecs++;
            document.getElementById('video-timer').innerText = `${String(Math.floor(videoSecs / 60)).padStart(2, '0')}:${String(videoSecs % 60).padStart(2, '0')}`;
        }, 1000);
    }

    function stopVideoRecording() { if (videoRecorder) videoRecorder.stop(); }

    function resetVideoRecording() {
        document.getElementById('video-done').classList.add('hidden');
        document.getElementById('video-idle').classList.remove('hidden');
        document.getElementById('btn-start-vid').classList.remove('hidden');
        document.getElementById('btn-stop-vid').classList.add('hidden');
        document.getElementById('btn-stop-vid').classList.remove('flex');
        document.getElementById('video-recording-badge').classList.add('hidden');
        document.getElementById('media-file-input').value = ""; 
    }

    // ================== ЛОГИКА ВОПРОСОВ ==================
    function addQuestion() {
        const html = `
            <div class="question-block bg-white border border-slate-200 rounded-2xl p-6 relative shadow-sm focus-within:border-blue-300 focus-within:ring-4 focus-within:ring-blue-50 transition-all group">
                <button type="button" onclick="this.closest('.question-block').remove(); triggerAutoSave();" class="absolute -top-3 -right-3 bg-white border border-slate-200 text-slate-300 hover:text-red-500 hover:border-red-200 w-8 h-8 rounded-full flex items-center justify-center shadow-sm transition-all opacity-0 group-hover:opacity-100 z-10" title="Удалить вопрос">✕</button>
                <div class="mb-4 pr-4">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Вопрос</label>
                    <input type="text" name="questions[${qIndex}][text]" onblur="triggerAutoSave()" class="w-full border border-slate-200 p-3 rounded-xl outline-none focus:border-blue-500 font-bold text-slate-700 bg-slate-50 focus:bg-white transition-colors" placeholder="Напишите вопрос..." required>
                </div>
                <div class="answers-container space-y-3 mb-4" id="answers-${qIndex}"></div>
                <button type="button" onclick="addAnswer(${qIndex})" class="text-xs font-bold text-blue-500 hover:text-blue-700 transition-colors uppercase tracking-wider">+ Добавить вариант</button>
            </div>
        `;
        document.getElementById('questions-container').insertAdjacentHTML('beforeend', html);
        addAnswer(qIndex); addAnswer(qIndex);
        qIndex++;
    }

    function addAnswer(qId) {
        const container = document.getElementById(`answers-${qId}`);
        const aCount = container.children.length;
        const html = `
            <div class="flex items-center gap-4 bg-slate-50 p-3 rounded-xl border border-slate-100 transition-all focus-within:bg-white focus-within:border-blue-200">
                <input type="hidden" name="questions[${qId}][answers][${aCount}][is_correct]" value="0">
                <label class="relative flex items-center justify-center cursor-pointer ml-1">
                    <input type="radio" name="correct_q_${qId}" value="1" class="peer sr-only ans-marker" required onchange="setCorrect(this); triggerAutoSave();">
                    <div class="w-5 h-5 rounded-full border-2 border-slate-300 peer-checked:border-blue-600 peer-checked:bg-blue-600 transition-colors shadow-inner"></div>
                    <svg class="absolute w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                </label>
                <input type="text" name="questions[${qId}][answers][${aCount}][text]" onblur="triggerAutoSave()" class="flex-1 border-none bg-transparent focus:ring-0 outline-none font-medium text-slate-700" placeholder="Вариант ответа..." required>
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
        @if(!isset($task) || empty($task->options['questions'])) 
            addQuestion(); 
        @endif 
    });

    function validateForm(e) {
        const qs = document.querySelectorAll('.question-block');
        if (qs.length === 0) { e.preventDefault(); showErr("Добавьте хотя бы один вопрос."); return false; }
        
        const fileInput = document.getElementById('media-file-input');
        const hasOldFile = {{ isset($task) && !empty($task->options['media_path']) ? 'true' : 'false' }};
        if (fileInput.files.length === 0 && !hasOldFile) {
            e.preventDefault(); showErr("Загрузите файл, запишите аудио или снимите видео!"); return false;
        }

        let valid = true;
        qs.forEach((q, i) => {
            const m = q.querySelectorAll('.ans-marker');
            if (m.length < 2) { valid = false; showErr(`В вопросе ${i+1} нужно минимум 2 варианта ответа.`); }
            let hasC = false; m.forEach(x => { if(x.checked) hasC = true; });
            if (!hasC) { valid = false; showErr(`В вопросе ${i+1} не выбран правильный ответ.`); }
        });
        if (!valid) { 
            e.preventDefault(); 
            const err = document.getElementById('error-message');
            err.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false; 
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
                    method: 'POST',
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