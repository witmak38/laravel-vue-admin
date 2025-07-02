<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();

            $table->string('imageable_type'); // Класс модели (Page, Product и т.д.)
            $table->unsignedBigInteger('imageable_id'); // ID модели

            $table->string('path');    // Путь к файлу
            $table->string('alt')->nullable();   // alt-текст для SEO
            $table->string('title')->nullable(); // Заголовок изображения
            $table->integer('sort_order')->default(0); // Порядок сортировки

            $table->index(['imageable_type', 'imageable_id']);
            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
