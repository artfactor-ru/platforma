@extends('layouts.app')
@section('header_title', 'Задание: ' . ($task->title ?? 'Без названия'))

@section('content')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .mw-arrow { transition: all 0.3s ease-in-out; opacity: 0; pointer-events: none; width: 0; margin: 0; }
    .mw-arrow.active { opacity: 1; pointer-events: auto; width: 140px; margin: 0 10px; }
    .mw-arrow.point-right { flex-direction: row; }
    .mw-arrow.point-left { flex-direction: row-reverse; }
    .mw-arrow.point-left svg { transform: rotate(180deg); }
    .timer-bar { transition: width linear; }
    /* Стили для аудио-плеера */
    #media-progress-bar { transition: width 0.1s linear; }
    @keyframes pulse { 0% { height: 10px; } 100% { height: 40px; } }
</style>

<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white p-8 rounded-xl shadow-sm border border-slate-200 relative overflow-hidden" id="main-card">
        
        <div class="mb-8 border-b border-slate-100 pb-4 flex justify-between items-center" id="task-header">
            <h1 class="text-2xl font-bold text-slate-800 mb-2">{{ $task->title ?? 'Задание' }}</h1>
            @if(($task->points ?? 0) > 0)
                <span class="bg-blue-50 text-blue-700 text-xs font-bold px-3 py-1 rounded border border-blue-200">Контрольная работа</span>
            @endif
        </div>
        
        <div id="task-container" data-type="{{ $task->type }}" data-points="{{ $task->points ?? 0 }}">
            
            @if(!in_array($task->type, ['image_match', 'odd_one', 'main_word', 'speed_test']) && $parsedContent && $parsedContent !== '<p><br></p>')
                @if($task->type === 'media_test')
                    <p class="text-lg text-slate-600 mb-6 bg-slate-50 p-4 rounded-lg border border-slate-100">{{ $task->content }}</p>
                @else
                    <div class="ql-snow mb-8">
                        <div class="ql-editor p-6 bg-slate-50 rounded-lg border border-slate-100 text-xl leading-loose text-slate-800" style="font-family: inherit;">
                            {!! $parsedContent !!}
                        </div>
                    </div>
                @endif
            @endif

            {{-- 1. РЕНДЕР: МУЛЬТИТЕСТЫ --}}
            @if($task->type === 'test')
                @php $totalQuestions = count($task->options); @endphp
                <div id="progress-container" class="mb-8">
                    <div class="flex justify-between items-end mb-3">
                        <span class="text-sm font-bold text-slate-500 uppercase" id="progress-text">Вопрос 1 из {{ $totalQuestions }}</span>
                        <span class="text-sm font-bold text-blue-600" id="progress-percentage">{{ round((1 / $totalQuestions) * 100) }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-3">
                        <div id="progress-bar" class="bg-blue-600 h-3 rounded-full transition-all duration-500" style="width: {{ (1 / $totalQuestions) * 100 }}%"></div>
                    </div>
                </div>
                <div class="space-y-6 mb-8 min-h-[250px]" id="questions-list">
                    @foreach($task->options as $qIndex => $question)
                        <div class="question-item bg-slate-50 p-6 rounded-xl border border-slate-100 {{ $qIndex === 0 ? 'block opacity-100 translate-x-0' : 'hidden opacity-0 translate-x-4' }} transition-all duration-300 transform" data-index="{{ $qIndex }}">
                            <h3 class="text-lg font-bold text-slate-800 mb-6">{{ $qIndex + 1 }}. {{ $question['text'] }}</h3>
                            <div class="space-y-3">
                                @foreach($question['answers'] as $aIndex => $answer)
                                    <label class="flex items-center gap-4 p-4 border border-slate-200 bg-white rounded-lg cursor-pointer hover:bg-slate-100 test-option-label">
                                        <input type="{{ $question['type'] === 'radio' ? 'radio' : 'checkbox' }}" name="q_{{ $qIndex }}" class="w-5 h-5 test-input" data-correct="{{ $answer['is_correct'] ?? '0' }}">
                                        <span class="text-slate-700">{{ $answer['text'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- 2. РЕНДЕР: ВИЗУАЛЬНЫЙ ТЕСТ --}}
            @if($task->type === 'image_match')
                @php $mode = $task->options['mode'] ?? 'text_to_image'; $items = $task->options['items'] ?? []; @endphp
                <div class="mb-8 text-center" id="image-test-container">
                    @if($mode === 'text_to_image')
                        <h2 class="text-4xl font-black text-slate-800 mb-8 tracking-wide">{{ $task->content }}</h2>
                    @else
                        <div class="mb-8 flex justify-center">
                            <img src="{{ asset('storage/' . $task->content) }}" alt="Question" class="max-h-64 object-contain rounded-xl shadow-sm border border-slate-200">
                        </div>
                    @endif
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 text-left">
                        @foreach($items as $index => $item)
                            <label class="relative cursor-pointer group test-option-label block h-full">
                                <input type="radio" name="image_answer" class="peer sr-only test-input" data-correct="{{ $item['is_correct'] }}">
                                <div class="h-full bg-white rounded-2xl border-2 border-slate-200 p-4 hover:border-blue-300 hover:shadow-md peer-checked:border-blue-600 peer-checked:ring-4 peer-checked:ring-blue-100 transition-all flex flex-col items-center justify-center gap-4">
                                    @if($mode === 'text_to_image') 
                                        <img src="{{ asset('storage/' . $item['image']) }}" class="w-full h-40 object-cover rounded-lg">
                                    @else 
                                        <span class="text-2xl font-bold text-slate-700 text-center w-full py-6">{{ $item['text'] }}</span> 
                                    @endif
                                </div>
                                <div class="absolute top-4 right-4 w-6 h-6 rounded-full border-2 border-slate-300 bg-white group-hover:border-blue-300 peer-checked:border-blue-600 peer-checked:bg-blue-600 transition-colors flex items-center justify-center shadow-sm pointer-events-none">
                                    <svg class="w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 3. РЕНДЕР: СОПОСТАВЛЕНИЕ ПАР (MATCH) --}}
            @if($task->type === 'match')
                @php $pairs = $task->options['pairs'] ?? []; $shuffledRight = collect($pairs)->shuffle(); @endphp
                <div class="mb-8" id="match-test-container">
                    <p class="text-slate-500 mb-6 text-center font-medium">Нажмите на элемент слева, а затем выберите ему пару справа.</p>
                    <div class="flex flex-col md:flex-row gap-8 justify-center items-stretch">
                        
                        <div class="flex-1 space-y-4 flex flex-col">
                            @foreach($pairs as $index => $pair)
                                <div class="match-left-item relative p-4 bg-white border-2 border-slate-200 rounded-xl cursor-pointer hover:border-blue-400 transition-all text-center flex-1 flex items-center justify-center min-h-[80px] select-none shadow-sm" data-id="{{ $pair['id'] }}">
                                    @if($pair['left_type'] === 'image') 
                                        <img src="{{ asset('storage/' . $pair['left_content']) }}" class="max-h-24 object-contain rounded">
                                    @else 
                                        <span class="text-lg font-medium text-slate-700">{{ $pair['left_content'] }}</span> 
                                    @endif
                                    <div class="match-badge absolute -top-3 -right-3 w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center opacity-0 transition-opacity shadow-sm"></div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="flex-1 space-y-4 flex flex-col">
                            @foreach($shuffledRight as $pair)
                                <div class="match-right-item relative p-4 bg-white border-2 border-slate-200 rounded-xl cursor-pointer hover:border-blue-400 transition-all text-center flex-1 flex items-center justify-center min-h-[80px] select-none shadow-sm" data-id="{{ $pair['id'] }}">
                                    @if($pair['right_type'] === 'image') 
                                        <img src="{{ asset('storage/' . $pair['right_content']) }}" class="max-h-24 object-contain rounded">
                                    @else 
                                        <span class="text-lg font-medium text-slate-700">{{ $pair['right_content'] }}</span> 
                                    @endif
                                    <div class="match-badge absolute -top-3 -left-3 w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center opacity-0 transition-opacity shadow-sm"></div>
                                </div>
                            @endforeach
                        </div>

                    </div>
                </div>
            @endif

            {{-- 4. РЕНДЕР: НАЙДИ ЛИШНЕЕ (С ОПЦИОНАЛЬНЫМ ОБЪЯСНЕНИЕМ) --}}
            @if($task->type === 'odd_one')
                <div class="mb-8 text-center" id="odd-test-container">
                    <h2 class="text-3xl font-bold text-slate-800 mb-8">{{ $task->content ?: 'Какое слово в этом ряду лишнее?' }}</h2>
                    
                    <div class="flex flex-wrap justify-center gap-4 mb-10">
                        @foreach($task->options['items'] ?? [] as $index => $item)
                            <label class="relative cursor-pointer group test-option-label">
                                <input type="radio" name="odd_answer" class="peer sr-only test-input" data-correct="{{ $item['is_odd'] }}">
                                <div class="bg-white rounded-xl border-2 border-slate-200 px-8 py-4 text-xl font-bold text-slate-700 hover:border-blue-400 hover:shadow-md peer-checked:border-blue-600 peer-checked:text-blue-700 peer-checked:bg-blue-50 transition-all duration-200">
                                    {{ $item['text'] }}
                                </div>
                            </label>
                        @endforeach
                    </div>

                    @if(!empty($task->options['explanations']))
                        <div class="bg-slate-50 p-6 rounded-xl border border-slate-200 text-left max-w-2xl mx-auto shadow-sm">
                            <h3 class="text-lg font-bold text-slate-800 mb-4">Почему вы выбрали это слово?</h3>
                            <div class="space-y-3">
                                @foreach($task->options['explanations'] as $eIndex => $exp)
                                    <label class="flex items-center gap-3 cursor-pointer p-3 bg-white border border-slate-200 rounded-lg hover:bg-slate-100 transition-colors exp-option-label">
                                        <input type="radio" name="exp_answer" class="w-4 h-4 text-blue-600 focus:ring-blue-500 exp-input" data-correct="{{ $exp['is_correct'] }}">
                                        <span class="text-slate-700 font-medium">{{ $exp['text'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- 5. РЕНДЕР: ГЛАВНОЕ СЛОВО И СТРЕЛОЧКА --}}
            @if($task->type === 'main_word')
                <div class="mb-8 space-y-6" id="mw-test-container">
                    <p class="text-slate-500 mb-6 text-center font-medium">Кликните на слово, чтобы сделать его главным, и впишите вопрос, который задается зависимому.</p>
                    
                    @foreach($task->options['pairs'] ?? [] as $pIndex => $pair)
                        <div class="mw-pair flex items-center justify-center p-6 bg-slate-50 border border-slate-200 rounded-xl shadow-sm" data-main="{{ $pair['main'] }}" data-q="{{ mb_strtolower(trim($pair['question'])) }}">
                            
                            <button type="button" class="mw-btn w-40 py-3 bg-white border-2 border-slate-200 rounded-lg text-lg font-bold text-slate-700 transition-all hover:border-blue-300" data-val="1">
                                {{ $pair['word1'] }}
                            </button>
                            
                            <div class="mw-arrow flex items-center overflow-hidden relative">
                                <div class="w-full h-0.5 bg-blue-400 absolute top-1/2 -translate-y-1/2 z-0"></div>
                                <svg class="w-6 h-6 text-blue-500 relative z-10 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                                <input type="text" class="mw-input relative z-10 w-full text-center text-sm border-2 border-blue-400 bg-white rounded shadow-sm outline-none px-1 py-1 font-bold text-blue-700 ml-[-5px] focus:ring-2 focus:ring-blue-200" placeholder="вопрос?">
                            </div>

                            <button type="button" class="mw-btn w-40 py-3 bg-white border-2 border-slate-200 rounded-lg text-lg font-bold text-slate-700 transition-all hover:border-blue-300" data-val="2">
                                {{ $pair['word2'] }}
                            </button>

                        </div>
                    @endforeach
                </div>
            @endif

            {{-- 6. РЕНДЕР: ТЕСТ НА СКОРОСТЬ (СПРИНТ) --}}
            @if($task->type === 'speed_test')
                @php 
                    $baseTime = $task->options['base_time'] ?? 3; 
                    $questions = $task->options['questions'] ?? [];
                @endphp
                
                <div id="speed-lobby" class="text-center py-8">
                    <div class="inline-block bg-orange-100 text-orange-600 p-4 rounded-full mb-6">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h2 class="text-3xl font-black text-slate-800 mb-2">Тест на скорость</h2>
                    <p class="text-slate-500 mb-8">Вопросы переключаются автоматически. Выберите уровень сложности:</p>
                    
                    <div class="flex flex-col sm:flex-row justify-center gap-4 mb-8 max-w-2xl mx-auto">
                        <label class="flex-1 cursor-pointer group">
                            <input type="radio" name="speed_choice" value="1" class="peer sr-only" checked>
                            <div class="border-2 border-slate-200 rounded-xl p-4 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all">
                                <span class="text-xl font-bold block mb-1 text-slate-700">🚶 Нормально</span>
                                <span class="text-sm text-slate-500">{{ $baseTime }} сек / вопрос</span>
                                <span class="block mt-2 text-xs font-bold bg-slate-100 text-slate-600 rounded py-1 px-2">Баллы: x1</span>
                            </div>
                        </label>
                        <label class="flex-1 cursor-pointer group">
                            <input type="radio" name="speed_choice" value="1.5" class="peer sr-only">
                            <div class="border-2 border-slate-200 rounded-xl p-4 peer-checked:border-orange-500 peer-checked:bg-orange-50 transition-all">
                                <span class="text-xl font-bold block mb-1 text-slate-700">🏃 Быстро</span>
                                <span class="text-sm text-slate-500">{{ round($baseTime / 1.5, 1) }} сек / вопрос</span>
                                <span class="block mt-2 text-xs font-bold bg-orange-100 text-orange-600 rounded py-1 px-2">Баллы: x1.5</span>
                            </div>
                        </label>
                        <label class="flex-1 cursor-pointer group">
                            <input type="radio" name="speed_choice" value="2" class="peer sr-only">
                            <div class="border-2 border-slate-200 rounded-xl p-4 peer-checked:border-red-500 peer-checked:bg-red-50 transition-all">
                                <span class="text-xl font-bold block mb-1 text-slate-700">⚡ Экстрим</span>
                                <span class="text-sm text-slate-500">{{ round($baseTime / 2, 1) }} сек / вопрос</span>
                                <span class="block mt-2 text-xs font-bold bg-red-100 text-red-600 rounded py-1 px-2">Баллы: x2</span>
                            </div>
                        </label>
                    </div>
                    <button id="start-speed-btn" class="bg-blue-600 text-white px-10 py-4 rounded-xl font-bold text-lg hover:bg-blue-700 shadow-md hover:shadow-lg transition-all transform hover:-translate-y-1">НАЧАТЬ СПРИНТ</button>
                </div>

                <div id="speed-game" class="hidden">
                    <div class="absolute top-0 left-0 w-full h-2 bg-slate-100 rounded-t-xl z-20">
                        <div id="speed-timer-bar" class="h-2 bg-orange-500 rounded-t-xl timer-bar w-full"></div>
                    </div>
                    <div class="flex justify-between text-sm font-bold text-slate-400 mb-6 uppercase tracking-wider mt-4">
                        <span id="speed-q-counter">Вопрос 1 / {{ count($questions) }}</span>
                        <span id="speed-timer-text" class="text-orange-500"></span>
                    </div>

                    @foreach($questions as $qIndex => $q)
                        <div class="speed-question hidden" data-index="{{ $qIndex }}" data-answered="false">
                            <div class="flex justify-center items-center h-48 mb-8">
                                @if($q['type'] === 'image') 
                                    <img src="{{ asset('storage/' . $q['content']) }}" class="max-h-full rounded-xl shadow-sm border border-slate-200">
                                @else 
                                    <h2 class="text-4xl font-black text-slate-800 text-center">{{ $q['content'] }}</h2> 
                                @endif
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach($q['answers'] as $aIndex => $ans)
                                    <button class="speed-ans-btn bg-white border-2 border-slate-200 rounded-xl p-4 text-lg font-bold text-slate-700 hover:border-blue-400 hover:bg-blue-50 transition-colors" data-correct="{{ $ans['is_correct'] }}">
                                        {{ $ans['text'] }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- 7. НОВЫЙ РЕНДЕР: МЕДИА-ТЕСТ (АУДИО/ВИДЕО С ЛИМИТОМ) --}}
            @if($task->type === 'media_test')
                @php 
                    $mediaPath = $task->options['media_path'] ?? ''; 
                    $maxPlays = $task->options['max_plays'] ?? 0;
                    $ext = strtolower(pathinfo($mediaPath, PATHINFO_EXTENSION));
                    $isVideo = in_array($ext, ['mp4', 'webm', 'mov']);
                @endphp

                <div class="mb-10 bg-slate-800 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-slate-700"><div id="media-progress-bar" class="h-1 bg-blue-500 w-0"></div></div>
                    
                    @if($isVideo)
                        <video id="custom-media" class="w-full rounded-xl mb-4 bg-black" playsinline>
                            <source src="{{ asset('storage/' . $mediaPath) }}" type="video/{{ $ext === 'mov' ? 'quicktime' : $ext }}">
                        </video>
                    @else
                        <div class="h-24 bg-slate-700/50 rounded-xl mb-4 flex items-center justify-center overflow-hidden">
                            <div class="flex items-center gap-1 opacity-50" id="audio-visualizer">
                                @for($i=0; $i<20; $i++) <div class="w-1.5 bg-blue-400 rounded-full h-4" style="animation: none;"></div> @endfor
                            </div>
                        </div>
                        <audio id="custom-media" preload="auto">
                            <source src="{{ asset('storage/' . $mediaPath) }}">
                        </audio>
                    @endif

                    <div class="flex items-center justify-between">
                        <button id="media-play-btn" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white px-6 py-3 rounded-xl font-bold transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" /></svg>
                            <span>Слушать</span>
                        </button>

                        <div class="text-right">
                            <p class="text-sm text-slate-400 mb-1">Осталось попыток:</p>
                            @if($maxPlays > 0)
                                <div class="flex gap-1 justify-end" id="plays-dots">
                                    @for($i=0; $i<$maxPlays; $i++) <div class="w-3 h-3 rounded-full bg-blue-500"></div> @endfor
                                </div>
                            @else
                                <span class="text-blue-400 font-bold text-sm">∞ Безлимит</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="space-y-6 mb-8" id="questions-list">
                    @foreach($task->options['questions'] ?? [] as $qIndex => $question)
                        <div class="question-item bg-slate-50 p-6 rounded-xl border border-slate-100" data-index="{{ $qIndex }}">
                            <h3 class="text-lg font-bold text-slate-800 mb-6">{{ $qIndex + 1 }}. {{ $question['text'] }}</h3>
                            <div class="space-y-3">
                                @foreach($question['answers'] as $aIndex => $answer)
                                    <label class="flex items-center gap-4 p-4 border border-slate-200 bg-white rounded-lg cursor-pointer hover:bg-slate-100 test-option-label">
                                        <input type="radio" name="q_{{ $qIndex }}" class="w-5 h-5 test-input" data-correct="{{ $answer['is_correct'] ?? '0' }}">
                                        <span class="text-slate-700">{{ $answer['text'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>

        <div class="flex justify-between items-center border-t border-slate-100 pt-6 {{ $task->type === 'speed_test' ? 'hidden' : '' }}" id="action-buttons">
            @if($task->type === 'test')
                <button id="prev-btn" class="invisible text-slate-500 hover:text-blue-600 font-medium px-4 py-2 transition-colors">← Назад</button>
                <div class="flex gap-4">
                    <button id="next-btn" class="bg-blue-100 text-blue-700 px-8 py-3 rounded-lg font-medium hover:bg-blue-200 transition-colors {{ isset($totalQuestions) && $totalQuestions > 1 ? '' : 'hidden' }}">Далее →</button>
                    <button id="check-btn" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-medium shadow-sm hover:bg-blue-700 transition-colors {{ isset($totalQuestions) && $totalQuestions > 1 ? 'hidden' : '' }}">Завершить тест</button>
                </div>
            @else
                <button id="check-btn" class="ml-auto bg-blue-600 text-white px-8 py-3 rounded-lg font-medium shadow-sm hover:bg-blue-700 transition-colors">
                    Проверить ответы
                </button>
            @endif
        </div>
    </div>

    <div id="results-report" class="hidden transform transition-all duration-500 translate-y-4 opacity-0 bg-white p-8 rounded-xl shadow-sm border border-slate-200">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-slate-800" id="result-title">Результат</h2>
        </div>
        
        <p class="text-lg text-slate-600 mb-6 font-medium" id="result-desc"></p>
        
        <div id="cloze-table-container" class="hidden overflow-x-auto rounded-lg border border-slate-200 mb-6">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 text-sm border-b border-slate-200">
                        <th class="p-4 font-medium">№</th>
                        <th class="p-4 font-medium">Ваш ответ</th>
                        <th class="p-4 font-medium">Правильный ответ</th>
                        <th class="p-4 font-medium text-center">Статус</th>
                    </tr>
                </thead>
                <tbody id="results-tbody" class="text-sm divide-y divide-slate-100"></tbody>
            </table>
        </div>
        
        <div class="flex gap-4">
            <button onclick="location.reload()" class="bg-slate-100 text-slate-700 px-6 py-2.5 rounded-lg font-medium hover:bg-slate-200 transition-colors">Пройти заново</button>
            <a href="{{ route('dashboard') }}" class="bg-blue-50 text-blue-600 px-6 py-2.5 rounded-lg font-medium hover:bg-blue-100 transition-colors">К списку заданий</a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkBtn = document.getElementById('check-btn');
        const taskContainer = document.getElementById('task-container');
        const taskType = taskContainer.getAttribute('data-type');
        const maxTaskPoints = parseInt(taskContainer.getAttribute('data-points'), 10) || 0; 
        const isExam = maxTaskPoints > 0;
        
        // --- ЛОГИКА КАСТОМНОГО ПЛЕЕРА ДЛЯ MEDIA_TEST ---
        if (taskType === 'media_test') {
            const media = document.getElementById('custom-media');
            const playBtn = document.getElementById('media-play-btn');
            const progressBar = document.getElementById('media-progress-bar');
            const maxPlays = {{ $task->options['max_plays'] ?? 0 }};
            let playsLeft = maxPlays;
            let isPlaying = false;

            if (playBtn && media) {
                playBtn.addEventListener('click', () => {
                    if (isPlaying) return; 
                    if (maxPlays === 0 || playsLeft > 0) {
                        media.play();
                        isPlaying = true;
                        playBtn.classList.add('opacity-50', 'cursor-not-allowed');
                        playBtn.querySelector('span').innerText = 'Воспроизведение...';
                        
                        document.querySelectorAll('#audio-visualizer div').forEach(bar => {
                            bar.style.animation = `pulse ${0.5 + Math.random()}s infinite alternate`;
                        });
                    }
                });

                media.addEventListener('timeupdate', () => {
                    const percent = (media.currentTime / media.duration) * 100;
                    progressBar.style.width = percent + '%';
                });

                media.addEventListener('ended', () => {
                    isPlaying = false;
                    progressBar.style.width = '0%';
                    document.querySelectorAll('#audio-visualizer div').forEach(bar => bar.style.animation = 'none');
                    
                    if (maxPlays > 0) {
                        playsLeft--;
                        const dots = document.getElementById('plays-dots').children;
                        if(dots[playsLeft]) dots[playsLeft].classList.replace('bg-blue-500', 'bg-slate-600');

                        if (playsLeft <= 0) {
                            playBtn.disabled = true;
                            playBtn.classList.replace('bg-blue-600', 'bg-slate-700');
                            playBtn.classList.replace('hover:bg-blue-500', 'hover:bg-slate-700');
                            playBtn.querySelector('span').innerText = 'Лимит исчерпан';
                            return;
                        }
                    }
                    
                    playBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    playBtn.querySelector('span').innerText = 'Слушать еще раз';
                });
            }
        }
        
        if (taskType === 'test') {
            let currentStep = 0; 
            const questions = document.querySelectorAll('.question-item'); 
            const totalSteps = questions.length;
            const prevBtn = document.getElementById('prev-btn'); 
            const nextBtn = document.getElementById('next-btn');
            const progressBar = document.getElementById('progress-bar'); 
            const progressText = document.getElementById('progress-text'); 
            const progressPercentage = document.getElementById('progress-percentage');
            
            function updateWizard() {
                questions.forEach((q, index) => {
                    if (index === currentStep) { 
                        q.classList.remove('hidden'); 
                        setTimeout(() => { q.classList.remove('opacity-0', 'translate-x-4'); q.classList.add('opacity-100', 'translate-x-0'); }, 50); 
                    } else { 
                        q.classList.add('hidden', 'opacity-0', 'translate-x-4'); 
                        q.classList.remove('opacity-100', 'translate-x-0'); 
                    }
                });
                const percentage = Math.round(((currentStep + 1) / totalSteps) * 100);
                progressBar.style.width = percentage + '%'; 
                progressText.innerText = `Вопрос ${currentStep + 1} из ${totalSteps}`; 
                progressPercentage.innerText = percentage + '%';
                if (currentStep === 0) prevBtn.classList.add('invisible'); else prevBtn.classList.remove('invisible');
                if (currentStep === totalSteps - 1) { nextBtn.classList.add('hidden'); checkBtn.classList.remove('hidden'); } 
                else { nextBtn.classList.remove('hidden'); checkBtn.classList.add('hidden'); }
            }
            if (nextBtn) nextBtn.addEventListener('click', () => { if (currentStep < totalSteps - 1) { currentStep++; updateWizard(); } });
            if (prevBtn) prevBtn.addEventListener('click', () => { if (currentStep > 0) { currentStep--; updateWizard(); } });
        }

        if (taskType === 'match') {
            let selectedLeft = null; 
            let currentConnectionNumber = 1;
            const leftItems = document.querySelectorAll('.match-left-item'); 
            const rightItems = document.querySelectorAll('.match-right-item');
            
            leftItems.forEach(item => {
                item.addEventListener('click', function() {
                    if (this.hasAttribute('data-connected-to')) {
                        const rightConnected = document.querySelector(`.match-right-item[data-connected-to="${this.getAttribute('data-connected-to')}"]`);
                        if (rightConnected) {
                            rightConnected.removeAttribute('data-connected-to'); rightConnected.removeAttribute('data-matched-with'); 
                            rightConnected.classList.remove('border-blue-600', 'bg-blue-50'); rightConnected.querySelector('.match-badge').style.opacity = '0';
                        }
                        this.removeAttribute('data-connected-to'); this.classList.remove('border-blue-600', 'bg-blue-50'); 
                        this.querySelector('.match-badge').style.opacity = '0'; return;
                    }
                    leftItems.forEach(i => { if(!i.hasAttribute('data-connected-to')) i.classList.remove('ring-4', 'ring-blue-200', 'border-blue-500'); });
                    this.classList.add('ring-4', 'ring-blue-200', 'border-blue-500'); selectedLeft = this;
                });
            });
            
            rightItems.forEach(item => {
                item.addEventListener('click', function() {
                    if (!selectedLeft) {
                        if (this.hasAttribute('data-connected-to')) {
                            const oldLeft = document.querySelector(`.match-left-item[data-connected-to="${this.getAttribute('data-connected-to')}"]`);
                            if (oldLeft) { 
                                oldLeft.removeAttribute('data-connected-to'); oldLeft.classList.remove('border-blue-600', 'bg-blue-50'); 
                                oldLeft.querySelector('.match-badge').style.opacity = '0'; 
                            }
                            this.removeAttribute('data-connected-to'); this.removeAttribute('data-matched-with'); 
                            this.classList.remove('border-blue-600', 'bg-blue-50'); this.querySelector('.match-badge').style.opacity = '0';
                        }
                        return; 
                    }
                    if (this.hasAttribute('data-connected-to')) {
                        const oldLeft = document.querySelector(`.match-left-item[data-connected-to="${this.getAttribute('data-connected-to')}"]`);
                        if (oldLeft) { 
                            oldLeft.removeAttribute('data-connected-to'); oldLeft.classList.remove('border-blue-600', 'bg-blue-50'); 
                            oldLeft.querySelector('.match-badge').style.opacity = '0'; 
                        }
                    }
                    const linkId = 'conn_' + currentConnectionNumber;
                    selectedLeft.setAttribute('data-connected-to', linkId); selectedLeft.classList.remove('ring-4', 'ring-blue-200', 'border-blue-500'); 
                    selectedLeft.classList.add('border-blue-600', 'bg-blue-50');
                    const badgeLeft = selectedLeft.querySelector('.match-badge'); badgeLeft.innerText = currentConnectionNumber; badgeLeft.style.opacity = '1';
                    
                    this.setAttribute('data-connected-to', linkId); this.classList.add('border-blue-600', 'bg-blue-50');
                    const badgeRight = this.querySelector('.match-badge'); badgeRight.innerText = currentConnectionNumber; badgeRight.style.opacity = '1';
                    this.setAttribute('data-matched-with', selectedLeft.getAttribute('data-id'));
                    
                    currentConnectionNumber++; selectedLeft = null;
                });
            });
        }

        if (taskType === 'main_word') {
            document.querySelectorAll('.mw-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const pair = this.closest('.mw-pair');
                    const val = this.getAttribute('data-val'); 
                    
                    pair.querySelectorAll('.mw-btn').forEach(b => { 
                        b.classList.remove('border-blue-500', 'bg-blue-50', 'text-blue-700', 'shadow-md'); 
                        b.removeAttribute('data-selected'); 
                    });
                    
                    this.classList.add('border-blue-500', 'bg-blue-50', 'text-blue-700', 'shadow-md');
                    this.setAttribute('data-selected', 'true');
                    pair.setAttribute('data-user-main', val);

                    const arrow = pair.querySelector('.mw-arrow');
                    arrow.classList.remove('point-left', 'point-right');
                    arrow.classList.add('active', val === '1' ? 'point-right' : 'point-left');
                    setTimeout(() => { arrow.querySelector('.mw-input').focus(); }, 300);
                });
            });
        }

        if (taskType === 'speed_test') {
            const startBtn = document.getElementById('start-speed-btn');
            const lobby = document.getElementById('speed-lobby');
            const game = document.getElementById('speed-game');
            const questions = document.querySelectorAll('.speed-question');
            const bar = document.getElementById('speed-timer-bar');
            const txt = document.getElementById('speed-timer-text');
            const counter = document.getElementById('speed-q-counter');
            
            const baseTime = {{ $task->options['base_time'] ?? 3 }};
            let currentQ = 0;
            let timerInterval;
            let multiplier = 1;
            let speedScore = 0;

            if (startBtn) {
                startBtn.addEventListener('click', () => {
                    multiplier = parseFloat(document.querySelector('input[name="speed_choice"]:checked').value);
                    document.getElementById('task-header').classList.add('hidden');
                    lobby.classList.add('hidden');
                    game.classList.remove('hidden');
                    showQuestion(0);
                });
            }

            function showQuestion(index) {
                questions.forEach(q => q.classList.add('hidden'));
                if (index >= questions.length) return endSpeedTest();
                
                currentQ = index;
                counter.innerText = `Вопрос ${index + 1} / ${questions.length}`;
                questions[index].classList.remove('hidden');
                startTimer();
            }

            function startTimer() {
                clearInterval(timerInterval);
                const durationMs = (baseTime / multiplier) * 1000;
                let start = Date.now();
                
                bar.style.width = '100%';
                txt.innerText = (durationMs / 1000).toFixed(1) + 'с';
                
                timerInterval = setInterval(() => {
                    let passed = Date.now() - start;
                    let left = durationMs - passed;
                    
                    if (left <= 0) {
                        clearInterval(timerInterval);
                        bar.style.width = '0%';
                        txt.innerText = '0.0с';
                        handleAnswer(null);
                    } else {
                        bar.style.width = (left / durationMs * 100) + '%';
                        txt.innerText = (left / 1000).toFixed(1) + 'с';
                    }
                }, 50);
            }

            function handleAnswer(btn) {
                clearInterval(timerInterval);
                const qBlock = questions[currentQ];
                if(qBlock.getAttribute('data-answered') === 'true') return;
                qBlock.setAttribute('data-answered', 'true');

                qBlock.querySelectorAll('.speed-ans-btn').forEach(b => b.style.pointerEvents = 'none');

                if (btn) {
                    const isCorrect = btn.getAttribute('data-correct') === '1';
                    if (isCorrect) {
                        btn.classList.replace('border-slate-200', 'border-green-500');
                        btn.classList.add('bg-green-50', 'text-green-700');
                        speedScore++;
                    } else {
                        btn.classList.replace('border-slate-200', 'border-red-500');
                        btn.classList.add('bg-red-50', 'text-red-700');
                    }
                }
                
                setTimeout(() => { showQuestion(currentQ + 1); }, 600);
            }

            document.querySelectorAll('.speed-ans-btn').forEach(btn => {
                btn.addEventListener('click', function() { handleAnswer(this); });
            });

            function endSpeedTest() {
                game.classList.add('hidden');
                document.getElementById('task-header').classList.remove('hidden');
                
                const report = document.getElementById('results-report');
                report.classList.remove('hidden', 'translate-y-4', 'opacity-0');
                
                const maxRaw = questions.length;
                const finalMultiplier = multiplier;
                
                let earnedPoints = 0;
                if (isExam) {
                    const rawPoints = maxScore > 0 ? (speedScore / maxRaw) * maxTaskPoints : 0;
                    earnedPoints = Math.round((rawPoints * finalMultiplier) * 10) / 10;
                }

                const title = document.getElementById('result-title'); 
                const desc = document.getElementById('result-desc');
                
                title.innerText = 'Спринт завершен!'; 
                title.className = 'text-3xl font-black text-orange-600'; 
                
                if (isExam) {
                    desc.innerHTML = `Правильных ответов: <b>${speedScore} из ${maxRaw}</b><br>Множитель скорости: <b>x${finalMultiplier}</b><br><span class="text-orange-600 font-black mt-3 block text-3xl">Заработано баллов: ${earnedPoints}</span>`;
                } else {
                    desc.innerHTML = `Правильных ответов: <span class="text-orange-600 font-bold">${speedScore} из ${maxRaw}</span><br>Множитель: x${finalMultiplier}`;
                }
            }
        }

        if (checkBtn) {
            checkBtn.addEventListener('click', function() {
                let totalScore = 0; 
                let maxScore = 0;
                const resultsTbody = document.getElementById('results-tbody'); 
                resultsTbody.innerHTML = '';

                if (taskType === 'test' || taskType === 'media_test') {
                    const questions = document.querySelectorAll('.question-item'); 
                    maxScore = questions.length;
                    
                    if (!isExam && taskType === 'test') { 
                        document.getElementById('progress-container').classList.add('hidden'); 
                        questions.forEach(q => { q.classList.remove('hidden', 'opacity-0', 'translate-x-4'); q.classList.add('opacity-100', 'translate-x-0', 'mb-6'); }); 
                    }
                    
                    questions.forEach(question => {
                        let isQuestionCorrect = true; 
                        const labels = question.querySelectorAll('.test-option-label');
                        labels.forEach(label => {
                            const input = label.querySelector('.test-input'); 
                            const isCorrectOption = input.getAttribute('data-correct') === '1'; 
                            const isChecked = input.checked; 
                            input.disabled = true;
                            
                            if (isCorrectOption && isChecked) { if (!isExam) label.classList.add('bg-green-50', 'border-green-300'); } 
                            else if (isCorrectOption && !isChecked) { if (!isExam) label.classList.add('bg-yellow-50', 'border-yellow-300'); isQuestionCorrect = false; } 
                            else if (!isCorrectOption && isChecked) { if (!isExam) label.classList.add('bg-red-50', 'border-red-300'); isQuestionCorrect = false; }
                        });
                        if (isQuestionCorrect) totalScore++;
                    });
                } 

                else if (taskType === 'cloze') {
                    const inputs = document.querySelectorAll('.cloze-input'); 
                    maxScore = inputs.length;
                    inputs.forEach((input, index) => {
                        input.setAttribute('readonly', true); input.style.pointerEvents = 'none';
                        const correctAnswer = input.getAttribute('data-answer').trim().toLowerCase(); 
                        const originalCorrectAnswer = input.getAttribute('data-answer');
                        const userAnswer = input.value.trim().toLowerCase(); 
                        const originalUserAnswer = input.value.trim(); 
                        const isCorrect = userAnswer === correctAnswer;
                        
                        if (isCorrect) { if(!isExam) input.classList.add('correct'); totalScore++; } 
                        else { if(!isExam) input.classList.add('incorrect'); }
                        
                        if (!isExam) {
                            const statusHtml = isCorrect ? '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Верно</span>' : '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Ошибка</span>';
                            const tr = document.createElement('tr'); 
                            tr.innerHTML = `<td class="p-4 text-slate-500">${index + 1}</td><td class="p-4 font-medium ${isCorrect ? 'text-green-600' : 'text-red-500'}">${originalUserAnswer === '' ? 'пропущено' : originalUserAnswer}</td><td class="p-4 text-slate-700 font-medium">${originalCorrectAnswer}</td><td class="p-4 text-center">${statusHtml}</td>`;
                            resultsTbody.appendChild(tr);
                        }
                    });
                    if (!isExam) document.getElementById('cloze-table-container').classList.remove('hidden');
                } 
                
                else if (taskType === 'image_match') {
                    maxScore = 1; 
                    let isQuestionCorrect = false; 
                    let isAnswered = false;
                    const labels = document.querySelectorAll('.test-option-label');
                    labels.forEach(label => {
                        const input = label.querySelector('.test-input'); 
                        const isCorrectOption = input.getAttribute('data-correct') === '1'; 
                        const isChecked = input.checked; 
                        input.disabled = true;
                        if (isChecked) isAnswered = true;
                        const cardDiv = label.querySelector('div.bg-white');
                        
                        if (isCorrectOption && isChecked) { if (!isExam) cardDiv.classList.add('bg-green-50', 'border-green-500', 'text-green-700'); isQuestionCorrect = true; } 
                        else if (isCorrectOption && !isChecked) { if (!isExam) cardDiv.classList.add('bg-yellow-50', 'border-yellow-400', 'text-yellow-700'); } 
                        else if (!isCorrectOption && isChecked) { if (!isExam) cardDiv.classList.add('bg-red-50', 'border-red-500', 'text-red-700'); }
                    });
                    if (!isAnswered && !isExam) { labels.forEach(l => { if(l.querySelector('.test-input').getAttribute('data-correct') === '1') l.querySelector('div.bg-white').classList.add('bg-yellow-50', 'border-yellow-400', 'text-yellow-700'); }); }
                    if (isQuestionCorrect) totalScore = 1;
                } 
                
                else if (taskType === 'odd_one') {
                    const hasExp = document.querySelectorAll('.exp-input').length > 0;
                    maxScore = hasExp ? 2 : 1; 

                    document.querySelectorAll('.test-option-label').forEach(label => {
                        const input = label.querySelector('.test-input');
                        const isCorrectOption = input.getAttribute('data-correct') === '1';
                        input.disabled = true;
                        const div = label.querySelector('div');
                        
                        if (input.checked && isCorrectOption) { totalScore++; if(!isExam) div.classList.add('bg-green-50', 'border-green-500', 'text-green-700'); }
                        else if (input.checked && !isCorrectOption) { if(!isExam) div.classList.add('bg-red-50', 'border-red-500', 'text-red-700'); }
                        else if (!input.checked && isCorrectOption && !isExam) { div.classList.add('bg-yellow-50', 'border-yellow-400', 'text-yellow-700'); }
                    });

                    if (hasExp) {
                        document.querySelectorAll('.exp-option-label').forEach(label => {
                            const input = label.querySelector('.exp-input');
                            const isCorrectOption = input.getAttribute('data-correct') === '1';
                            input.disabled = true;
                            
                            if (input.checked && isCorrectOption) { totalScore++; if(!isExam) label.classList.add('bg-green-50', 'border-green-500'); }
                            else if (input.checked && !isCorrectOption) { if(!isExam) label.classList.add('bg-red-50', 'border-red-500'); }
                            else if (!input.checked && isCorrectOption && !isExam) { label.classList.add('bg-yellow-50', 'border-yellow-400'); }
                        });
                    }
                } 
                
                else if (taskType === 'match') {
                    const rightItems = document.querySelectorAll('.match-right-item'); 
                    maxScore = rightItems.length;
                    document.querySelectorAll('.match-left-item, .match-right-item').forEach(el => el.style.pointerEvents = 'none');
                    
                    rightItems.forEach(item => {
                        const actualId = item.getAttribute('data-id'); 
                        const matchedWithId = item.getAttribute('data-matched-with'); 
                        const isCorrect = actualId === matchedWithId;
                        
                        if (isCorrect) {
                            if (!isExam) { 
                                item.classList.replace('border-blue-600', 'border-green-500'); item.classList.replace('bg-blue-50', 'bg-green-50'); 
                                const correctLeft = document.querySelector(`.match-left-item[data-id="${actualId}"]`); 
                                if (correctLeft) { correctLeft.classList.replace('border-blue-600', 'border-green-500'); correctLeft.classList.replace('bg-blue-50', 'bg-green-50'); } 
                            }
                            totalScore++;
                        } else {
                            if (!isExam) {
                                item.classList.remove('border-blue-600', 'bg-blue-50'); item.classList.add('border-red-500', 'bg-red-50');
                                if (matchedWithId) { 
                                    const wrongLeft = document.querySelector(`.match-left-item[data-id="${matchedWithId}"]`); 
                                    if (wrongLeft) { wrongLeft.classList.remove('border-blue-600', 'bg-blue-50'); wrongLeft.classList.add('border-red-500', 'bg-red-50'); } 
                                }
                                const correctLeft = document.querySelector(`.match-left-item[data-id="${actualId}"]`); 
                                if (correctLeft && !correctLeft.hasAttribute('data-matched-with')) { correctLeft.classList.remove('border-slate-200'); correctLeft.classList.add('border-yellow-400', 'bg-yellow-50'); }
                            }
                        }
                    });
                } 
                
                else if (taskType === 'main_word') {
                    const pairs = document.querySelectorAll('.mw-pair');
                    maxScore = pairs.length * 2; 

                    pairs.forEach(pair => {
                        const actualMain = pair.getAttribute('data-main');
                        const userMain = pair.getAttribute('data-user-main');
                        const actualQ = pair.getAttribute('data-q');
                        const input = pair.querySelector('.mw-input');
                        const userQ = input.value.trim().toLowerCase();
                        
                        input.disabled = true;
                        pair.querySelectorAll('.mw-btn').forEach(b => b.style.pointerEvents = 'none');

                        if (userMain === actualMain) {
                            totalScore++;
                            if(!isExam && userMain) {
                                const btn = pair.querySelector(`.mw-btn[data-val="${userMain}"]`);
                                btn.classList.replace('border-blue-500', 'border-green-500');
                                btn.classList.replace('text-blue-700', 'text-green-700');
                                btn.classList.replace('bg-blue-50', 'bg-green-50');
                            }
                        } else if (!isExam) {
                            if (userMain) {
                                const btn = pair.querySelector(`.mw-btn[data-val="${userMain}"]`);
                                btn.classList.replace('border-blue-500', 'border-red-500');
                                btn.classList.replace('text-blue-700', 'text-red-700');
                                btn.classList.replace('bg-blue-50', 'bg-red-50');
                            }
                            const correctBtn = pair.querySelector(`.mw-btn[data-val="${actualMain}"]`);
                            correctBtn.classList.add('border-yellow-400', 'bg-yellow-50', 'text-yellow-700');
                        }

                        if (userQ === actualQ && userQ !== '') {
                            totalScore++;
                            if(!isExam) {
                                input.classList.replace('border-blue-400', 'border-green-500');
                                input.classList.replace('text-blue-700', 'text-green-700');
                                input.parentElement.querySelector('svg').classList.replace('text-blue-500', 'text-green-500');
                                input.parentElement.querySelector('.bg-blue-400').classList.replace('bg-blue-400', 'bg-green-400');
                            }
                        } else if (!isExam) {
                            input.classList.replace('border-blue-400', 'border-red-500');
                            input.classList.replace('text-blue-700', 'text-red-700');
                            input.parentElement.querySelector('svg').classList.replace('text-blue-500', 'text-red-500');
                            input.parentElement.querySelector('.bg-blue-400').classList.replace('bg-blue-400', 'bg-red-400');
                            
                            input.value = actualQ; 
                            input.classList.add('bg-red-50');
                            
                            const arrow = pair.querySelector('.mw-arrow');
                            if (!arrow.classList.contains('active')) {
                                arrow.classList.add('active', actualMain === '1' ? 'point-right' : 'point-left');
                            }
                        }
                    });
                }

                document.getElementById('action-buttons').classList.add('hidden');
                const report = document.getElementById('results-report');
                report.classList.remove('hidden', 'translate-y-4', 'opacity-0');
                const title = document.getElementById('result-title'); 
                const desc = document.getElementById('result-desc');

                if (isExam) {
                    let earnedPoints = maxScore > 0 ? (totalScore / maxScore) * maxTaskPoints : 0; 
                    earnedPoints = Math.round(earnedPoints * 10) / 10;
                    title.innerText = 'Задание завершено'; 
                    title.className = 'text-3xl font-bold text-slate-800'; 
                    desc.innerHTML = `Ответы сохранены. <br><span class="text-blue-600 font-bold mt-3 block text-2xl">Заработано баллов: ${earnedPoints} / ${maxTaskPoints}</span>`;
                } else {
                    if (totalScore === maxScore && maxScore > 0) { 
                        title.innerText = '🎉 Отлично!'; 
                        title.className = 'text-3xl font-bold text-green-600'; 
                        desc.innerHTML = 'Вы ответили абсолютно верно.'; 
                    } else { 
                        title.innerText = 'Есть ошибки'; 
                        title.className = 'text-3xl font-bold text-slate-800'; 
                        desc.innerHTML = `Правильных ответов: <span class="text-blue-600 font-bold">${totalScore} из ${maxScore}</span>`; 
                    }
                }
                
                setTimeout(() => { report.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 100);
            });
        }
    });
</script>
@endsection