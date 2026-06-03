<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    // Добавили homework_duration_minutes
    protected $fillable = [
        'course_id', 
        'title', 
        'sort_order', 
        'duration_minutes', 
        'is_strict_order', 
        'homework_duration_minutes' // <--- ВОТ ЭТО ПОЛЕ
    ];

    // Урок принадлежит курсу
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // У урока много упражнений (заданий)
    public function tasks()
    {
        return $this->hasMany(Task::class)->orderBy('sort_order');
    }
}