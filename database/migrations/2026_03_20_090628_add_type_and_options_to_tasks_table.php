<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Добавляем тип задания (по умолчанию ставим cloze - пропуски)
            $table->string('type')->default('cloze')->after('id');
            // Добавляем JSON-колонку для вариантов ответов (nullable, так как для пропусков она не нужна)
            $table->json('options')->nullable()->after('content');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['type', 'options']);
        });
    }
};