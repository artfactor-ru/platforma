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
    Schema::create('courses', function (Blueprint $table) {
        $table->id();
        $table->string('title'); // Название курса
        $table->string('subject')->nullable(); // Предмет (Математика, Английский)
        $table->text('description')->nullable(); // Краткое описание
        $table->enum('status', ['draft', 'published'])->default('draft'); // Черновик или Готов
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
