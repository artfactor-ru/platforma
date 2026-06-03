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
    Schema::table('tasks', function (Blueprint $table) {
        // Добавляем привязку к уроку (nullable, чтобы старые задания без уроков не сломались)
        $table->foreignId('lesson_id')->nullable()->constrained()->cascadeOnDelete();
        $table->integer('sort_order')->default(0); // Для перетаскивания внутри урока
    });
}

public function down()
{
    Schema::table('tasks', function (Blueprint $table) {
        $table->dropForeign(['lesson_id']);
        $table->dropColumn(['lesson_id', 'sort_order']);
    });
}
};
