@extends('layouts.app')

@section('header_title', 'Создание нового курса')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl p-10 shadow-sm border border-slate-200">
        <div class="text-center mb-8">
            <span class="text-4xl mb-4 block">📚</span>
            <h1 class="text-2xl font-bold text-slate-800">Начнем создание курса</h1>
            <p class="text-slate-500">Введите название. Позже вы сможете добавить уроки и упражнения.</p>
        </div>

        <form action="{{ route('admin.courses.store') }}" method="POST">
            @csrf
            <div class="mb-6">
                <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide">Название курса</label>
                <input type="text" name="title" required autofocus
                       class="w-full border border-slate-300 rounded-xl p-4 outline-none focus:ring-2 focus:ring-blue-500 text-lg" 
                       placeholder="Напр: Математика 5 класс / Разговорный английский">
            </div>

            <div class="flex flex-col gap-3">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-100 transition-all">
                    Создать курс и перейти к урокам →
                </button>
                <a href="{{ route('admin.courses.index') }}" class="text-center py-2 text-slate-400 hover:text-slate-600 font-medium">
                    Отмена
                </a>
            </div>
        </form>
    </div>
</div>
@endsection