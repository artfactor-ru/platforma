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
    Schema::table('lessons', function (Blueprint $table) {
        $table->integer('homework_duration_minutes')->nullable(); // Время на ДЗ
    });

    Schema::table('tasks', function (Blueprint $table) {
        $table->boolean('is_homework')->default(false); // Признак: Классная работа или ДЗ
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            //
        });
    }
};
