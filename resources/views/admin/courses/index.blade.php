@extends('layouts.app')

@section('header_title', 'Библиотека курсов')

@section('content')
<div class="max-w-7xl mx-auto">
    
    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center gap-3">
            <span class="font-bold">{{ session('success') }}</span>
        </div>
    @endif

    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Ваши курсы</h1>
            <p class="text-slate-500 text-sm">Управляйте учебными программами и следите за прогрессом</p>
        </div>
        
        <a href="{{ route('admin.courses.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-blue-100 transition-all flex items-center gap-2">
            <span>+</span> Создать новый курс
        </a>
    </div>

    @if($courses->isEmpty())
        <div class="bg-white border-2 border-dashed border-slate-200 rounded-3xl p-20 text-center">
            <div class="text-6xl mb-6">📭</div>
            <h3 class="text-xl font-bold text-slate-700 mb-2">Библиотека пока пуста</h3>
            <p class="text-slate-500 mb-8 max-w-sm mx-auto">Создайте свой первый структурированный курс и наполните его интерактивными уроками.</p>
            <a href="{{ route('admin.courses.create') }}" class="text-blue-600 font-bold hover:underline">Добавить первый курс →</a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($courses as $course)
                <div class="bg-white border border-slate-200 rounded-2xl p-6 hover:shadow-xl hover:border-blue-200 transition-all group relative overflow-hidden flex flex-col h-full">
                    <div class="absolute top-0 left-0 w-full h-1.5 {{ $course->status === 'published' ? 'bg-green-500' : 'bg-amber-400' }}"></div>
                    
                    <div class="flex justify-between items-start mb-4">
                        <span class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $course->subject ?? 'Без предмета' }}</span>
                        <span class="px-2 py-1 rounded text-[10px] font-black uppercase {{ $course->status === 'published' ? 'bg-green-50 text-green-600' : 'bg-amber-50 text-amber-600' }}">
                            {{ $course->status === 'published' ? 'Готов' : 'Черновик' }}
                        </span>
                    </div>

                    <h3 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-blue-600 transition-colors">
                        {{ $course->title }}
                    </h3>
                    
                    <div class="flex items-center gap-4 text-sm text-slate-500 mb-6">
                        <div class="flex items-center gap-1">
                            <span>📖</span> {{ $course->lessons->count() }} уроков
                        </div>
                    </div>

                    <div class="mt-auto flex items-center gap-2">
                        <a href="{{ route('admin.courses.edit', $course->id) }}" class="flex-1 bg-slate-50 hover:bg-blue-50 text-slate-600 hover:text-blue-600 text-center py-2.5 rounded-xl font-bold text-sm border border-slate-100 transition-all">
                            Редактировать
                        </a>
                        
                        <form action="{{ route('admin.courses.destroy', $course->id) }}" method="POST" onsubmit="return confirm('Удалить курс навсегда? Это действие необратимо.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-2.5 bg-slate-50 hover:bg-red-50 text-slate-400 hover:text-red-500 rounded-xl border border-slate-100 transition-all" title="Удалить курс">
                                🗑️
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection