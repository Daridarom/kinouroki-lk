<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Приближённая копия таблиц боевого сайта (схема восстановлена по страницам,
 * точные имена колонок на проде могут отличаться — сверить при переносе).
 * НОВЫХ полей эта миграция не добавляет.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('films', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();          // на проде: /films/{id|slug}
            $t->string('title');
            $t->string('quality');                  // «Качество»: Упорство, Дружба…
            $t->text('description')->nullable();    // HTML из TinyMCE
            $t->string('poster')->nullable();       // путь в /storage
            $t->string('triller')->nullable();      // mp4 в /storage или embed-ссылка
            $t->string('video')->nullable();        // embed Rutube / YouTube
            $t->json('photos')->nullable();
            $t->json('links')->nullable();          // кнопки «Скачать фильм…»
            $t->text('time')->nullable();           // вкладка «Сроки»
            $t->unsignedInteger('lesson_id')->nullable();
            $t->timestamp('published_at')->nullable();
            $t->timestamps();
        });

        // Категории = «Начальная / Основная / Средняя школа» (меню кабинета: /films/category/{id})
        Schema::create('film_categories', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->timestamps();
        });

        Schema::create('category_film', function (Blueprint $t) {
            $t->foreignId('film_id')->constrained()->cascadeOnDelete();
            $t->foreignId('film_category_id')->constrained()->cascadeOnDelete();
            $t->primary(['film_id', 'film_category_id']);
        });

        Schema::create('news', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();
            $t->string('title');
            $t->string('image')->nullable();
            $t->text('body')->nullable();
            $t->json('categories')->nullable();
            $t->json('links')->nullable();
            $t->string('film_slug')->nullable();
            $t->string('published_label')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news');
        Schema::dropIfExists('category_film');
        Schema::dropIfExists('film_categories');
        Schema::dropIfExists('films');
    }
};
