@extends('layouts.app')
@section('header_title', 'Выбор шаблона')

@section('content')
<div class="max-w-[1400px] mx-auto pb-20">
    
    <div class="mb-8">
        @if(isset($lesson))
            <a href="{{ request('is_homework') ? route('admin.lessons.homework', $lesson->id) : route('admin.lessons.builder', $lesson->id) }}" 
               class="text-blue-500 text-sm font-bold flex items-center gap-2 hover:underline w-fit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Назад в урок: "{{ $lesson->title }}"
            </a>
        @else
            <a href="{{ route('dashboard') }}" class="text-slate-400 text-sm font-bold hover:text-slate-600 transition-colors hover:underline">← К списку всех заданий</a>
        @endif
    </div>

    <div class="mb-8">
        @if(isset($lesson))
            <div class="mb-3 flex items-center gap-3">
                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest">
                    {{ request('is_homework') ? 'Домашнее задание' : 'Классная работа' }}
                </span>
            </div>
            <h1 class="text-2xl font-black text-slate-800">
                Новое упражнение 
                <span class="text-slate-400 font-medium ml-2">для урока №{{ $lesson->id }} "{{ $lesson->title }}"</span>
            </h1>
        @else
            <h1 class="text-2xl font-black text-slate-800">Новое упражнение (в общий банк)</h1>
        @endif
    </div>

    <div class="flex flex-col lg:flex-row gap-10">

        <div class="flex-1 flex flex-col items-center justify-center bg-white border-2 border-dashed border-slate-200 rounded-[2.5rem] p-10 text-center min-h-[400px]">
            <div class="w-24 h-24 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center text-5xl mb-6 shadow-inner border border-slate-100">
                🧩
            </div>
            <h2 class="text-2xl font-black text-slate-800 mb-3 tracking-tight">Выберите шаблон</h2>
            <p class="text-slate-500 max-w-md font-medium leading-relaxed">
                Для начала работы кликните на один из виджетов в панели справа. Форма настройки мгновенно появится здесь.
            </p>
        </div>

        <div class="w-full lg:w-[340px] flex-shrink-0">
            <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-200 sticky top-8">
                <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest mb-2 text-center">Выбор шаблона</h3>
                <p class="text-[10px] text-slate-400 text-center mb-8 uppercase tracking-wider font-bold italic">Выберите виджет для создания</p>

                <div class="grid grid-cols-2 gap-3">
                    @foreach($widgetTypes as $widget)
                        <a href="{{ route('admin.create', ['type' => $widget['id'], 'lesson_id' => $lesson->id ?? null, 'is_homework' => request('is_homework', 0)]) }}" 
                           class="aspect-square bg-slate-50 border-2 border-slate-100 rounded-3xl p-4 flex flex-col items-center justify-center text-center group hover:border-blue-400 hover:bg-white hover:shadow-xl transition-all">
                            <div class="text-3xl mb-2 grayscale group-hover:grayscale-0 transition-all transform group-hover:scale-110">{{ $widget['icon'] }}</div>
                            <span class="text-[10px] font-black uppercase tracking-tighter leading-tight text-slate-400 group-hover:text-blue-600 transition-colors">{{ $widget['name'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
</div>
@endsection