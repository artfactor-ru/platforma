<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    // Добавили lesson_id, sort_order и is_homework
    protected $fillable = [
        'lesson_id', 
        'sort_order', 
        'title', 
        'type', 
        'content', 
        'options', 
        'points',
        'is_homework' // <--- ВОТ ОНО
    ];

    protected $casts = [
        'options' => 'array',
        'is_homework' => 'boolean', // Опционально, но полезно для типизации
    ];

    // Упражнение принадлежит уроку
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}