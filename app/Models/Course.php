<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'subject', 'description', 'status'];

    // У курса много уроков
    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('sort_order');
    }
}