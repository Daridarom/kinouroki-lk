<?php

use App\Http\Controllers\FilmController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

// Адреса повторяют боевой lk.kinouroki.org
Route::redirect('/', '/films')->name('home');

Route::get('/films', [FilmController::class, 'index'])->name('films.index');
Route::get('/films/category/{category}', [FilmController::class, 'index'])->name('films.category');
Route::get('/films/{film}', [FilmController::class, 'show'])->name('films.show');

Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{news}', [NewsController::class, 'show'])->name('news.show');

foreach (['about', 'faq', 'documents', 'research', 'webinars', 'video' => 'video-programms', 'journals' => 'jurnals', 'contacts'] as $key => $uri) {
    $key = is_int($key) ? $uri : $key;
    Route::get('/'.$uri, fn () => app(PageController::class)->show($key))->name('pages.'.$key);
}

// Разделы, которые в копию не переносились, — на боевой сайт
foreach (['practies', 'initiatives', 'statistics/activities', 'lessons', 'users', 'login', 'register'] as $uri) {
    Route::redirect('/'.$uri, config('kinouroki.prod_url').'/'.$uri);
}
