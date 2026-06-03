<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Языковая платформа</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .correct { border-color: #22c55e !important; background-color: #f0fdf4 !important; color: #166534; pointer-events: none; font-weight: bold; }
        .incorrect { border-color: #ef4444 !important; background-color: #fef2f2 !important; color: #991b1b; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased flex h-screen overflow-hidden">

    <aside class="w-64 bg-white border-r border-slate-200 flex flex-col justify-between hidden md:flex">
        <div>
            <div class="h-16 flex items-center px-6 border-b border-slate-200">
                <span class="text-xl font-bold text-slate-700">Платформа</span>
            </div>
            
            <nav class="p-4 space-y-1">
                <a href="{{ route('dashboard') }}" class="block px-4 py-2.5 rounded-lg mb-4 {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }} transition-colors">
                    📚 Все задания
                </a>

                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 mt-4 px-4">Создание</div>
                
                <a href="{{ route('admin.create', ['type' => 'cloze']) }}" class="block px-4 py-2.5 rounded-lg {{ request()->fullUrlIs(route('admin.create', ['type' => 'cloze'])) ? 'bg-blue-50 text-blue-700 font-medium' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }} transition-colors text-sm">
                    📝 Текст с пропусками
                </a>
                
                <a href="{{ route('admin.create', ['type' => 'test']) }}" class="block px-4 py-2.5 rounded-lg {{ request()->fullUrlIs(route('admin.create', ['type' => 'test'])) ? 'bg-blue-50 text-blue-700 font-medium' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }} transition-colors text-sm">
                    ✅ Мультитест
                </a>

                <a href="{{ route('admin.create', ['type' => 'image_match']) }}" class="block px-4 py-2.5 rounded-lg {{ request()->fullUrlIs(route('admin.create', ['type' => 'image_match'])) ? 'bg-blue-50 text-blue-700 font-medium' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }} transition-colors text-sm">
                    🖼️ Визуальный тест
                </a>

                <a href="{{ route('admin.create', ['type' => 'match']) }}" class="block px-4 py-2.5 rounded-lg {{ request()->fullUrlIs(route('admin.create', ['type' => 'match'])) ? 'bg-blue-50 text-blue-700 font-medium' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }} transition-colors text-sm">
                    🔗 Сопоставление пар
                </a>

                <a href="{{ route('admin.create', ['type' => 'odd_one']) }}" class="block px-4 py-2.5 rounded-lg {{ request()->fullUrlIs(route('admin.create', ['type' => 'odd_one'])) ? 'bg-blue-50 text-blue-700 font-medium' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }} transition-colors text-sm">
                    🔍 Найди лишнее
                </a>

                <a href="{{ route('admin.create', ['type' => 'main_word']) }}" class="block px-4 py-2.5 rounded-lg {{ request()->fullUrlIs(route('admin.create', ['type' => 'main_word'])) ? 'bg-blue-50 text-blue-700 font-medium' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }} transition-colors text-sm">
                    🎯 Главное слово
                </a>

                <a href="{{ route('admin.create', ['type' => 'speed_test']) }}" class="block px-4 py-2.5 rounded-lg {{ request()->fullUrlIs(route('admin.create', ['type' => 'speed_test'])) ? 'bg-blue-50 text-blue-700 font-medium' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }} transition-colors text-sm">
                    ⚡ Тест на скорость
                </a>

                <a href="{{ route('admin.create', ['type' => 'media_test']) }}" class="block px-4 py-2.5 rounded-lg {{ request()->fullUrlIs(route('admin.create', ['type' => 'media_test'])) ? 'bg-blue-50 text-blue-700 font-medium' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }} transition-colors text-sm">🎧 Аудио/Видео тест
                </a>
            </nav>
        </div>
        
        <div class="p-4 border-t border-slate-200">
            <div class="flex items-center gap-3 px-4 py-2">
                <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-sm">
                    М
                </div>
                <div class="text-sm">
                    <p class="font-medium text-slate-700">Мой Профиль</p>
                    <p class="text-xs text-slate-500">Методист</p>
                </div>
            </div>
        </div>
    </aside>

    <main class="flex-1 flex flex-col h-screen overflow-hidden">
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 shadow-sm z-10">
            <h2 class="text-lg font-semibold text-slate-700">@yield('header_title', 'Рабочий стол')</h2>
            
            <div class="flex items-center gap-4">
                <span class="text-sm text-slate-500">{{ date('d.m.Y') }}</span>
                <button class="p-2 text-slate-400 hover:text-slate-600 rounded-full hover:bg-slate-100 transition-colors">
                    🔔
                </button>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-6">
            @yield('content')
        </div>
    </main>
    
</body>
</html>