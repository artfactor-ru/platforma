<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;

use App\Http\Controllers\CourseController;

Route::get('/', [TaskController::class, 'index'])->name('dashboard');

Route::get('/admin/tasks/create/{type?}', [TaskController::class, 'create'])->name('admin.create');
Route::post('/admin/tasks', [TaskController::class, 'store'])->name('admin.store');
Route::delete('/admin/tasks/{task}', [TaskController::class, 'destroy'])->name('admin.destroy');

Route::get('/student/tasks/{task}', [TaskController::class, 'show'])->name('student.show');

Route::get('/admin/tasks/{task}/edit', [TaskController::class, 'edit'])->name('admin.edit');
Route::put('/admin/tasks/{task}', [TaskController::class, 'update'])->name('admin.update');


Route::prefix('admin')->group(function () {
    // Список всех курсов
    Route::get('/courses', [CourseController::class, 'index'])->name('admin.courses.index');
    
    // Создание курса
    Route::get('/courses/create', [CourseController::class, 'create'])->name('admin.courses.create');
    Route::post('/courses/store', [CourseController::class, 'store'])->name('admin.courses.store');
    
    // Редактирование курса (та самая страница со списком уроков)
    Route::get('/courses/{course}/edit', [CourseController::class, 'edit'])->name('admin.courses.edit');
    Route::put('/courses/{course}/update', [CourseController::class, 'update'])->name('admin.courses.update');

    // Удаление урока
	Route::delete('/lessons/{lesson}', [CourseController::class, 'destroyLesson'])->name('admin.lessons.destroy');
    
    // УДАЛЕНИЕ КУРСА
    Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->name('admin.courses.destroy');
    
    // Работа с уроками внутри курса (AJAX или обычные формы)
    Route::post('/courses/{course}/lessons', [CourseController::class, 'addLesson'])->name('admin.courses.addLesson');
    Route::post('/lessons/reorder', [CourseController::class, 'reorderLessons'])->name('admin.lessons.reorder');
    Route::post('/tasks/reorder', [CourseController::class, 'reorderTasks'])->name('admin.tasks.reorder');

    // Обновление конкретного урока (название через AJAX)
    Route::put('/lessons/{lesson}', [CourseController::class, 'updateLesson'])->name('admin.lessons.update');

    // Конструктор урока (экран с перетаскиванием виджетов)
    Route::get('/lessons/{lesson}/builder', [CourseController::class, 'editLesson'])->name('admin.lessons.builder');

    // Добавление задачи в урок
    Route::post('/lessons/{lesson}/tasks', [CourseController::class, 'addTaskToLesson'])->name('admin.lessons.addTask');

    // Конструктор урока (Классная работа)
	Route::get('/lessons/{lesson}/builder', [CourseController::class, 'editLesson'])->name('admin.lessons.builder');

	// Конструктор домашнего задания (НОВЫЙ)
	Route::get('/lessons/{lesson}/homework', [CourseController::class, 'editHomework'])->name('admin.lessons.homework');

	// Создание задачи (передаем тип виджета и опционально ID урока)
	Route::get('/admin/tasks/create/{type?}/{lesson_id?}', [TaskController::class, 'create'])->name('admin.create');
	Route::post('/admin/tasks', [TaskController::class, 'store'])->name('admin.store');

	Route::put('/admin/tasks/{task}/autosave', [\App\Http\Controllers\TaskController::class, 'autosave'])->name('admin.tasks.autosave');
});