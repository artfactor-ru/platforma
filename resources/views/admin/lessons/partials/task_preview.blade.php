<div class="student-mockup-container text-left bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mx-auto max-w-2xl">
    
    {{-- Шапка как у ученика --}}
    <div class="px-6 py-4 border-b border-slate-50 flex justify-between items-center bg-white">
        <h4 class="text-sm font-bold text-slate-800">{{ $task->title }}</h4>
        @if($task->points > 0)
            <span class="text-[9px] px-2 py-1 bg-blue-50 text-blue-600 rounded-lg font-black uppercase tracking-tighter border border-blue-100">Контрольная работа</span>
        @endif
    </div>

    {{-- Контент задания --}}
    <div class="p-6 bg-[#f8fafc]/50">
        
        {{-- ТИП: ТЕСТ (Мультитест) --}}
        @if($task->type === 'test')
            @php $firstQuestion = $task->options[0] ?? null; @endphp
            @if($firstQuestion)
                <div class="mb-6">
                    <div class="flex justify-between items-end mb-2">
                        <span class="text-[10px] font-black text-blue-600 uppercase tracking-widest">Вопрос 1 из {{ count($task->options) }}</span>
                        <span class="text-[10px] font-black text-blue-600">50%</span>
                    </div>
                    <div class="w-full h-2 bg-slate-200/50 rounded-full overflow-hidden border border-slate-100">
                        <div class="bg-blue-500 h-full rounded-full" style="width: 50%"></div>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm mb-6">
                    <p class="text-base font-bold text-slate-800">1. {{ $firstQuestion['text'] ?? 'Заголовок вопроса' }}</p>
                </div>

                <div class="space-y-3">
                    @foreach($firstQuestion['answers'] ?? [] as $ans)
                        <div class="flex items-center gap-4 p-4 bg-white border-2 {{ $ans['is_correct'] ? 'border-green-400 ring-4 ring-green-50' : 'border-slate-100' }} rounded-2xl transition-all shadow-sm">
                            <div class="w-5 h-5 rounded-full border-2 {{ $ans['is_correct'] ? 'border-green-500 bg-green-500 shadow-[0_0_0_3px_white_inset]' : 'border-slate-300' }} flex-shrink-0"></div>
                            <span class="text-sm font-bold {{ $ans['is_correct'] ? 'text-green-700' : 'text-slate-600' }}">{{ $ans['text'] }}</span>
                            @if($ans['is_correct'])
                                <div class="ml-auto bg-green-100 text-green-600 p-1 rounded-full">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

        {{-- ТИП: CLOZE (Пропуски) --}}
        @elseif($task->type === 'cloze')
            <div class="bg-white p-8 rounded-[2rem] border border-slate-100 shadow-sm leading-relaxed text-slate-700 text-base">
                {!! preg_replace('/{([^}]+)}/', '<span class="inline-flex items-center justify-center px-4 py-1 bg-blue-50 border-2 border-dashed border-blue-300 text-blue-700 rounded-xl font-black mx-1 shadow-sm">$1</span>', $task->content) !!}
            </div>

        {{-- ТИП: MATCH (Сопоставление) --}}
        @elseif($task->type === 'match')
            <div class="grid grid-cols-2 gap-6 relative">
                @foreach(collect($task->options['pairs'] ?? [])->take(3) as $pair)
                    {{-- Левая часть --}}
                    <div class="p-4 bg-white border-2 border-slate-100 rounded-2xl shadow-sm flex items-center justify-center min-h-[100px] text-center">
                        @if(($pair['left_type'] ?? 'text') === 'image')
                            <img src="{{ asset('storage/' . $pair['left_content']) }}" class="max-h-16 rounded-lg object-contain">
                        @else
                            <span class="text-sm font-black text-slate-700">{{ $pair['left_content'] }}</span>
                        @endif
                    </div>
                    {{-- Правая часть --}}
                    <div class="p-4 bg-blue-50 border-2 border-blue-200 rounded-2xl shadow-sm flex items-center justify-center min-h-[100px] text-center ring-4 ring-blue-50/50">
                        @if(($pair['right_type'] ?? 'text') === 'image')
                            <img src="{{ asset('storage/' . $pair['right_content']) }}" class="max-h-16 rounded-lg object-contain">
                        @else
                            <span class="text-sm font-black text-blue-700">{{ $pair['right_content'] }}</span>
                        @endif
                    </div>
                @endforeach
            </div>

        {{-- ТИП: IMAGE_MATCH (Визуальный тест) --}}
        @elseif($task->type === 'image_match')
             <div class="space-y-4 text-center">
                @if($task->options['mode'] === 'image_to_text')
                    <img src="{{ asset('storage/' . $task->content) }}" class="w-full h-48 object-cover rounded-3xl mb-4 border-4 border-white shadow-md">
                    <div class="grid grid-cols-2 gap-3">
                        @foreach($task->options['items'] as $item)
                            <div class="p-4 bg-white border-2 {{ $item['is_correct'] ? 'border-green-400 bg-green-50' : 'border-slate-100' }} rounded-2xl text-xs font-black uppercase tracking-tight">
                                {{ $item['text'] }}
                            </div>
                        @endforeach
                    </div>
                @endif
             </div>

        {{-- ТИП: ODD ONE (Лишнее слово) --}}
        @elseif($task->type === 'odd_one')
             <div class="grid grid-cols-2 gap-3">
                @foreach($task->options['items'] ?? [] as $item)
                    <div class="p-4 rounded-2xl border-2 {{ $item['is_odd'] ? 'border-orange-400 bg-orange-50 text-orange-700' : 'border-slate-100 bg-white text-slate-700' }} text-sm font-black uppercase tracking-tight text-center shadow-sm">
                        {{ $item['text'] }}
                    </div>
                @endforeach
             </div>

        @else
            <div class="py-12 text-center bg-white rounded-3xl border border-dashed border-slate-200">
                <div class="text-5xl mb-4">🧩</div>
                <p class="text-slate-400 text-xs font-black uppercase tracking-[0.2em]">Превью в разработке</p>
                <div class="mt-4 inline-block px-3 py-1 bg-slate-100 rounded-full text-[10px] font-bold text-slate-500 uppercase tracking-tighter">
                    Тип: {{ $task->type }}
                </div>
            </div>
        @endif
    </div>

    {{-- Футер --}}
    <div class="px-8 py-5 border-t border-slate-50 bg-white flex justify-end">
        <div class="px-8 py-3 bg-blue-600 text-white rounded-2xl text-xs font-black uppercase tracking-widest flex items-center gap-3 shadow-lg shadow-blue-100">
            Далее 
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
        </div>
    </div>
</div>