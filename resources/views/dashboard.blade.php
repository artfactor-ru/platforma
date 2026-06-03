@extends('layouts.app')

@section('header_title', 'Все задания')

@section('content')
<div class="max-w-7xl mx-auto">
    
    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center gap-3 shadow-sm transform transition-all">
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row flex-wrap gap-4 mb-8">
        <div class="flex-1 flex gap-4 min-w-[300px]">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" placeholder="Поиск задания..." class="w-full border border-slate-300 pl-10 pr-4 py-2.5 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none shadow-sm transition-shadow">
            </div>
            <select class="border border-slate-300 px-4 py-2.5 rounded-xl outline-none bg-white shadow-sm hover:border-slate-400 cursor-pointer transition-colors">
                <option>Уровень: Любой</option>
                <option>Начальный (A1-A2)</option>
                <option>Средний (B1-B2)</option>
            </select>
        </div>
        
        <div class="relative group">
            <button class="h-full bg-blue-600 text-white px-6 py-2.5 rounded-xl hover:bg-blue-700 transition-colors shadow-sm flex items-center gap-2 font-medium">
                <span>+ Новое задание</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform group-hover:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
            </button>
            
            <div class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-xl border border-slate-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 transform origin-top-right scale-95 group-hover:scale-100">
                <div class="p-2 space-y-1">
                    <a href="{{ route('admin.create', ['type' => 'cloze']) }}" class="flex items-center gap-3 px-3 py-2.5 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors font-medium">
                        <span class="text-xl">📝</span> Текст с пропусками
                    </a>
                    <a href="{{ route('admin.create', ['type' => 'test']) }}" class="flex items-center gap-3 px-3 py-2.5 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors font-medium">
                        <span class="text-xl">✅</span> Мультитест
                    </a>
                    <a href="{{ route('admin.create', ['type' => 'image_match']) }}" class="flex items-center gap-3 px-3 py-2.5 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors font-medium">
                        <span class="text-xl">🖼️</span> Визуальный тест
                    </a>
                    <a href="{{ route('admin.create', ['type' => 'match']) }}" class="flex items-center gap-3 px-3 py-2.5 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors font-medium">
                        <span class="text-xl">🔗</span> Сопоставление пар
                    </a>
                    <a href="{{ route('admin.create', ['type' => 'odd_one']) }}" class="flex items-center gap-3 px-3 py-2.5 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors font-medium">
                        <span class="text-xl">🔍</span> Найди лишнее
                    </a>
                    <a href="{{ route('admin.create', ['type' => 'main_word']) }}" class="flex items-center gap-3 px-3 py-2.5 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors font-medium">
                        <span class="text-xl">🎯</span> Главное слово
                    </a>
                    <a href="{{ route('admin.create', ['type' => 'speed_test']) }}" class="flex items-center gap-3 px-3 py-2.5 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors font-medium">
                        <span class="text-xl">⚡</span> Тест на скорость
                    </a>
                    <a href="{{ route('admin.create', ['type' => 'media_test']) }}" class="flex items-center gap-3 px-3 py-2.5 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors font-medium">
                        <span class="text-xl">🎧</span> Аудио/Видео тест
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($tasks as $task)
            @php
                // Словари для красивого вывода иконок и названий всех 6 типов
                $typeIcons = ['cloze' => '📝', 'test' => '✅', 'image_match' => '🖼️', 'match' => '🔗', 'odd_one' => '🔍', 'main_word' => '🎯'];
                $typeNames = ['cloze' => 'Текст с пропусками', 'test' => 'Мультитест', 'image_match' => 'Визуальный тест', 'match' => 'Сопоставление', 'odd_one' => 'Найди лишнее', 'main_word' => 'Главное слово'];
                
                $icon = $typeIcons[$task->type] ?? '📝';
                $typeName = $typeNames[$task->type] ?? 'Задание';
            @endphp

            <div class="flex flex-col bg-white border border-slate-200 rounded-2xl p-6 hover:shadow-lg hover:border-blue-200 transition-all duration-300 group">
                
                <div class="flex justify-between items-start mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-xl shadow-sm">
                            {{ $icon }}
                        </div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ $typeName }}</span>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        @if(($task->points ?? 0) > 0)
                            <span class="text-xs font-bold text-blue-700 bg-blue-50 px-2.5 py-1.5 rounded-md border border-blue-100 shadow-sm">
                                {{ $task->points }} баллов
                            </span>
                        @else
                            <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1.5 rounded-md border border-emerald-100 shadow-sm">
                                Тренировка
                            </span>
                        @endif
                    </div>
                </div>
                
                <h3 class="text-lg font-bold text-slate-800 mb-2 group-hover:text-blue-600 transition-colors line-clamp-1">
                    {{ $task->title ?? 'Задание без названия' }}
                </h3>
                
                <p class="text-slate-500 text-sm line-clamp-2 mb-4 flex-1">
                    @if($task->type === 'cloze')
                        {{ Str::limit(strip_tags(preg_replace('/{[^}]+}/', '___', $task->content)), 90) }}
                    @elseif($task->type === 'image_match')
                        Визуальный тест на сопоставление картинок и перевода.
                    @elseif($task->type === 'match')
                        Задание на поиск логических пар и сопоставление элементов.
                    @elseif($task->type === 'odd_one')
                        {{ $task->content ?: 'Какое слово в этом ряду лишнее?' }}
                    @elseif($task->type === 'main_word')
                        Синтаксический разбор: поиск главного слова и постановка вопроса.
                    @else
                        {{ Str::limit(strip_tags($task->content), 90) }}
                    @endif
                </p>
                
                <div class="mb-5 flex gap-2 flex-wrap">
                    @if($task->type === 'test')
                        <span class="text-xs font-medium text-slate-500 bg-slate-50 border border-slate-200 px-2 py-1 rounded">
                            Вопросов: {{ is_array($task->options) ? count($task->options) : 0 }}
                        </span>
                    @elseif($task->type === 'match')
                        <span class="text-xs font-medium text-slate-500 bg-slate-50 border border-slate-200 px-2 py-1 rounded">
                            Пар: {{ isset($task->options['pairs']) ? count($task->options['pairs']) : 0 }}
                        </span>
                    @elseif($task->type === 'odd_one')
                        <span class="text-xs font-medium text-slate-500 bg-slate-50 border border-slate-200 px-2 py-1 rounded">
                            Слов в ряду: {{ isset($task->options['items']) ? count($task->options['items']) : 0 }}
                        </span>
                    @elseif($task->type === 'main_word')
                        <span class="text-xs font-medium text-slate-500 bg-slate-50 border border-slate-200 px-2 py-1 rounded">
                            Пар слов: {{ isset($task->options['pairs']) ? count($task->options['pairs']) : 0 }}
                        </span>
                    @endif
                </div>
                
                <div class="flex justify-between items-center mt-auto pt-4 border-t border-slate-100">
                    <a href="{{ route('student.show', $task->id) }}" class="inline-flex items-center gap-1 text-blue-600 text-sm font-bold hover:text-blue-800 transition-colors">
                        Пройти задание 
                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                    
                    <div class="flex items-center gap-1">
                        <a href="{{ route('admin.edit', $task->id) }}" class="text-slate-400 hover:text-blue-500 p-2 rounded-lg hover:bg-blue-50 transition-colors" title="Редактировать">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" /></svg>
                        </a>

                        <form action="{{ route('admin.destroy', $task->id) }}" method="POST" onsubmit="return confirm('Вы точно хотите удалить это задание?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-slate-400 hover:text-red-500 p-2 rounded-lg hover:bg-red-50 transition-colors" title="Удалить">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        @empty
            <div class="col-span-full bg-white border-2 border-dashed border-slate-200 rounded-2xl p-12 text-center flex flex-col items-center justify-center">
                <span class="text-5xl mb-4">📭</span>
                <h3 class="text-xl font-bold text-slate-700 mb-2">Банк заданий пуст</h3>
                <p class="text-slate-500 mb-6 max-w-md">Создайте свой первый интерактивный тест, текст с пропусками или визуальное сопоставление прямо сейчас.</p>
                <a href="{{ route('admin.create', ['type' => 'cloze']) }}" class="inline-flex items-center gap-2 bg-blue-600 text-white px-6 py-3 rounded-xl font-medium hover:bg-blue-700 transition-colors shadow-sm">
                    Создать первое задание
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection