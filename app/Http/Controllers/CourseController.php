<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Task;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    // Показать все курсы (Библиотека)
    public function index()
    {
        $courses = Course::latest()->get();
        return view('admin.courses.index', compact('courses'));
    }

    // Показать форму создания нового курса
    public function create()
    {
        return view('admin.courses.create');
    }

    // Сохранить новый курс в базу
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $course = Course::create([
            'title' => $request->title,
            'status' => 'draft',
        ]);

        return redirect()->route('admin.courses.edit', $course->id);
    }

    // Главная рабочая область (Курс)
    public function edit(Course $course)
    {
        $course->load(['lessons' => function($query) {
            $query->orderBy('sort_order');
        }]);
        
        return view('admin.courses.edit', compact('course'));
    }

    // Обновление курса (теперь поддерживает тихое AJAX автосохранение)
    public function update(Request $request, Course $course)
    {
        $course->update($request->only(['title', 'subject', 'description', 'status']));
        
        // Если запрос пришел из JS (на лету), отвечаем просто "Ок"
        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Изменения сохранены');
    }

    // Удаление урока
    public function destroyLesson(Lesson $lesson)
    {
        $lesson->delete();
        return response()->json(['success' => true]);
    }

    // Удаление курса целиком
    public function destroy(Course $course)
    {
        $course->delete();
        return redirect()->route('admin.courses.index')->with('success', 'Курс удален');
    }

    // Быстрое добавление урока в курс
    public function addLesson(Request $request, Course $course)
    {
        $request->validate(['title' => 'required|string']);

        $lesson = $course->lessons()->create([
            'title' => $request->title,
            'sort_order' => $course->lessons()->count() + 1
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'lesson' => $lesson]);
        }

        return back()->with('success', 'Урок добавлен');
    }

    // СОХРАНЕНИЕ СОРТИРОВКИ УРОКОВ
    public function reorderLessons(Request $request)
    {
        foreach ($request->ids as $index => $id) {
            Lesson::where('id', $id)->update(['sort_order' => $index + 1]);
        }
        return response()->json(['success' => true]);
    }

    // Обновление урока (теперь сохраняет и время ДЗ)
    public function updateLesson(Request $request, Lesson $lesson)
    {
        $lesson->update($request->only(['title', 'duration_minutes', 'is_strict_order', 'homework_duration_minutes']));
        return response()->json(['success' => true]);
    }

    // Открытие конструктора урока
    public function editLesson(Lesson $lesson)
    {
        $lesson->load(['tasks' => function($q) {
            $q->orderBy('sort_order');
        }, 'course']);
        
        $allTasks = Task::whereNull('lesson_id')->latest()->get();
        return view('admin.lessons.builder', compact('lesson', 'allTasks'));
    }

    // Привязка задачи из банка (Поддерживает и урок, и ДЗ)
    public function addTaskToLesson(Request $request, Lesson $lesson)
    {
        $task = Task::findOrFail($request->task_id);
        $isHomework = $request->boolean('is_homework', false); // Получаем флаг ДЗ
        
        $task->update([
            'lesson_id' => $lesson->id,
            'is_homework' => $isHomework,
            'sort_order' => $lesson->tasks()->where('is_homework', $isHomework)->count() + 1
        ]);
        return back()->with('success', 'Упражнение добавлено');
    }

    // СОХРАНЕНИЕ СОРТИРОВКИ УПРАЖНЕНИЙ
    public function reorderTasks(Request $request)
    {
        foreach ($request->ids as $index => $id) {
            Task::where('id', $id)->update(['sort_order' => $index + 1]);
        }
        return response()->json(['success' => true]);
    }

    // Открытие конструктора ДОМАШНЕГО ЗАДАНИЯ
    public function editHomework(Lesson $lesson)
    {
        $lesson->load(['tasks' => function($q) { $q->orderBy('sort_order'); }, 'course']);
        $allTasks = Task::whereNull('lesson_id')->latest()->get();
        return view('admin.lessons.homework', compact('lesson', 'allTasks'));
    }
}