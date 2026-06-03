<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Lesson;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // Массив шаблонов вынесен отдельно, чтобы не дублировать код
    private $widgetTypes = [
        ['id' => 'cloze', 'name' => 'Текст с пропусками', 'icon' => '📝'],
        ['id' => 'test', 'name' => 'Мультитест', 'icon' => '✅'],
        ['id' => 'image_match', 'name' => 'Визуальный тест', 'icon' => '🖼️'],
        ['id' => 'match', 'name' => 'Сопоставление пар', 'icon' => '🔗'],
        ['id' => 'odd_one', 'name' => 'Найди лишнее', 'icon' => '🔍'],
        ['id' => 'main_word', 'name' => 'Главное слово', 'icon' => '🎯'],
        ['id' => 'speed_test', 'name' => 'Тест на скорость', 'icon' => '⚡'],
        ['id' => 'media_test', 'name' => 'Аудио/Видео тест', 'icon' => '🎧'],
    ];

    private $views = [
        'cloze' => 'admin.create', // Твой основной файл для Cloze
        'test' => 'admin.create_test',
        'image_match' => 'admin.create_image_test',
        'match' => 'admin.create_match',
        'odd_one' => 'admin.create_odd',
        'main_word' => 'admin.create_main_word',
        'speed_test' => 'admin.create_speed_test',
        'media_test' => 'admin.create_media_test'
    ];

    public function index()
    {
        $tasks = Task::latest()->get();
        return view('dashboard', compact('tasks'));
    }

    // Экран СОЗДАНИЯ упражнения
    public function create(Request $request, $type = 'blank', $lesson_id = null) 
    {
        // Умный поиск: берем ID из маршрута (/blank/2) ИЛИ из GET-параметра (?lesson_id=2)
        $id = $lesson_id ?? $request->lesson_id;
        
        // Ищем урок, если ID найден
        $lesson = $id ? Lesson::find($id) : null;
        
        $widgetTypes = $this->widgetTypes;
        
        if ($type === 'blank') {
            return view('admin.create_blank', compact('type', 'lesson', 'widgetTypes'));
        }

        $viewName = $this->views[$type] ?? 'admin.create';
        return view($viewName, compact('type', 'lesson', 'widgetTypes'));
    }

    // Экран РЕДАКТИРОВАНИЯ упражнения
    public function edit(Task $task)
    {
        $lesson = $task->lesson_id ? Lesson::find($task->lesson_id) : null;
        $type = $task->type;
        $widgetTypes = $this->widgetTypes;

        $viewName = $this->views[$task->type] ?? 'admin.create';
        return view($viewName, compact('task', 'lesson', 'type', 'widgetTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required',
        ]);
        
        [$content, $options] = $this->processTaskData($request);
        
        // 1. Создаем задачу и ПРИКРЕПЛЯЕМ К УРОКУ (lesson_id)
        $task = Task::create([
            'title' => $request->title,
            'type' => $request->type,
            'points' => $request->points ?? 0,
            'content' => $content,
            'options' => $options,
            'lesson_id' => $request->lesson_id, // Вот главная связка!
            'is_homework' => $request->boolean('is_homework', false),
        ]);

        // 2. Если задача создана для урока - возвращаемся в конструктор урока
        if ($task->lesson_id) {
            // Пересчитываем порядок (чтобы она упала в конец списка)
            $task->update([
                'sort_order' => Task::where('lesson_id', $task->lesson_id)
                                    ->where('is_homework', $task->is_homework)
                                    ->count()
            ]);

            // Возврат в ДЗ или Классную работу
            if ($task->is_homework) {
                return redirect()->route('admin.lessons.homework', $task->lesson_id)->with('success', 'Упражнение добавлено в ДЗ');
            }
            return redirect()->route('admin.lessons.builder', $task->lesson_id)->with('success', 'Упражнение добавлено в урок');
        }

        // Если создавали просто из общего банка (без урока)
        return redirect()->route('dashboard')->with('success', 'Задание успешно создано!');
    }

    public function update(Request $request, Task $task)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);
        
        [$content, $options] = $this->processTaskData($request, $task);
        
        $task->update([
            'title' => $request->title,
            'points' => $request->points ?? 0,
            'content' => $content,
            'options' => $options
        ]);

        // Возвращаемся в урок после редактирования
        if ($task->lesson_id) {
            if ($task->is_homework) {
                return redirect()->route('admin.lessons.homework', $task->lesson_id)->with('success', 'Обновлено!');
            }
            return redirect()->route('admin.lessons.builder', $task->lesson_id)->with('success', 'Обновлено!');
        }

        return redirect()->route('dashboard')->with('success', 'Задание успешно обновлено!');
    }

    public function show(Task $task)
    {
        $parsedContent = $task->content;
        
        if ($task->type === 'cloze') {
            $parsedContent = preg_replace_callback('/{([^}]+)}/', function ($matches) {
                $correctAnswer = strip_tags($matches[1]); 
                $length = mb_strlen($correctAnswer);
                return '<input type="text" class="cloze-input border-b-2 border-slate-400 outline-none text-center px-1 mx-1 bg-transparent transition-colors duration-200 focus:border-blue-500 font-normal" style="width: ' . ($length * 1.2 + 1) . 'em;" data-answer="' . htmlspecialchars($correctAnswer) . '">';
            }, $task->content);
        }
        
        return view('student.show', compact('parsedContent', 'task'));
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return back()->with('success', 'Упражнение удалено!');
    }

    // МЕТОД АВТОСОХРАНЕНИЯ (AJAX)
    public function autosave(Request $request, Task $task) {
        $task->update($request->only(['title', 'content', 'points']));
        return response()->json(['success' => true]);
    }

    // ТВОЙ ОРИГИНАЛЬНЫЙ ПАРСЕР ДАННЫХ
    private function processTaskData(Request $request, ?Task $task = null)
    {
        $type = $task ? $task->type : $request->type;
        $content = $request->content ?? '';
        $options = $task ? $task->options : null;

        if ($type === 'test') {
            $options = $request->questions;
        } 
        elseif ($type === 'image_match') {
            $mode = $request->image_mode;
            if ($mode === 'image_to_text') {
                if ($request->hasFile('main_image')) $content = $request->file('main_image')->store('tasks', 'public');
                else $content = $task ? $task->content : '';
            }
            $items = [];
            if ($request->has('answers')) {
                foreach ($request->answers as $index => $answer) {
                    $item = ['is_correct' => $answer['is_correct'] ?? '0', 'text' => $answer['text'] ?? null];
                    if ($mode === 'text_to_image') {
                        if ($request->hasFile("answers.{$index}.image")) $item['image'] = $request->file("answers.{$index}.image")->store('tasks', 'public');
                        else $item['image'] = $task ? ($task->options['items'][$index]['image'] ?? null) : null;
                    }
                    $items[] = $item;
                }
            }
            $options = ['mode' => $mode, 'items' => $items];
        } 
        elseif ($type === 'match') {
            $pairs = [];
            if ($request->has('pairs')) {
                foreach ($request->pairs as $index => $pair) {
                    $left = $pair['left_text'] ?? ''; $right = $pair['right_text'] ?? '';
                    if (($pair['left_type'] ?? 'text') === 'image') {
                        if ($request->hasFile("pairs.{$index}.left_image")) $left = $request->file("pairs.{$index}.left_image")->store('tasks', 'public');
                        else $left = $task ? ($task->options['pairs'][$index]['left_content'] ?? '') : '';
                    }
                    if (($pair['right_type'] ?? 'text') === 'image') {
                        if ($request->hasFile("pairs.{$index}.right_image")) $right = $request->file("pairs.{$index}.right_image")->store('tasks', 'public');
                        else $right = $task ? ($task->options['pairs'][$index]['right_content'] ?? '') : '';
                    }
                    $pairs[] = ['id' => $task ? ($task->options['pairs'][$index]['id'] ?? uniqid()) : uniqid(), 'left_type' => $pair['left_type'] ?? 'text', 'left_content' => $left, 'right_type' => $pair['right_type'] ?? 'text', 'right_content' => $right];
                }
            }
            $options = ['pairs' => $pairs];
        } 
        elseif ($type === 'odd_one') {
            $items = []; $explanations = [];
            if ($request->has('items')) {
                foreach ($request->items as $item) $items[] = ['text' => $item['text'] ?? '', 'is_odd' => $item['is_odd'] ?? '0'];
            }
            if ($request->has('has_explanation') && $request->has('explanations')) {
                foreach ($request->explanations as $exp) $explanations[] = ['text' => $exp['text'] ?? '', 'is_correct' => $exp['is_correct'] ?? '0'];
            }
            $options = ['items' => $items, 'explanations' => $explanations];
        } 
        elseif ($type === 'main_word') {
            $pairs = [];
            if ($request->has('mw_pairs')) {
                foreach ($request->mw_pairs as $pair) {
                    $pairs[] = ['word1' => $pair['word1'] ?? '', 'word2' => $pair['word2'] ?? '', 'main' => $pair['main'] ?? '1', 'question' => $pair['question'] ?? ''];
                }
            }
            $options = ['pairs' => $pairs];
        }
        elseif ($type === 'speed_test') {
            $baseTime = $request->base_time ?? 3;
            $questions = [];
            if ($request->has('questions')) {
                foreach ($request->questions as $index => $q) {
                    $qContent = $q['text'] ?? '';
                    if (($q['type'] ?? 'text') === 'image') {
                        if ($request->hasFile("questions.{$index}.image")) $qContent = $request->file("questions.{$index}.image")->store('tasks', 'public');
                        else $qContent = $task ? ($task->options['questions'][$index]['content'] ?? '') : '';
                    }
                    $answers = [];
                    if (isset($q['answers'])) {
                        foreach ($q['answers'] as $aIndex => $ans) {
                            $answers[] = ['text' => $ans['text'] ?? '', 'is_correct' => $ans['is_correct'] ?? '0'];
                        }
                    }
                    $questions[] = ['type' => $q['type'] ?? 'text', 'content' => $qContent, 'answers' => $answers];
                }
            }
            $options = ['base_time' => $baseTime, 'questions' => $questions];
        }
        elseif ($type === 'media_test') {
            $mediaPath = $task ? ($task->options['media_path'] ?? '') : '';
            if ($request->hasFile('media_file')) {
                $mediaPath = $request->file('media_file')->store('tasks_media', 'public');
            }
            
            $maxPlays = $request->max_plays ?? 0;
            $questions = $request->questions ?? [];
            
            $options = [
                'media_path' => $mediaPath,
                'max_plays' => $maxPlays,
                'questions' => $questions
            ];
            $content = $request->content ?? ''; 
        }

        return [$content, $options];
    }
}