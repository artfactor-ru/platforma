<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('lessons', function (Blueprint $table) {
        $table->id();
        $table->foreignId('course_id')->constrained()->cascadeOnDelete(); // Привязка к курсу
        $table->string('title'); // Название урока
        $table->integer('sort_order')->default(0); // Для перетаскивания Drag&Drop
        $table->integer('duration_minutes')->nullable(); // Продолжительность (из твоего 2-го скрина)
        $table->boolean('is_strict_order')->default(false); // Произвольный или Строгий порядок прохождения
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
