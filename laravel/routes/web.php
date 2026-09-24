<?php

use App\Http\Controllers\CabinetController;
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

// Кабинет (данные — примеры, см. CabinetController)
Route::get('/users/0', [CabinetController::class, 'profile'])->name('cabinet.profile');
Route::get('/practies', [CabinetController::class, 'practices'])->name('cabinet.practices');
Route::get('/practies/user/0', fn () => app(CabinetController::class)->empty('Мои социальные практики'))->name('cabinet.practices.user');
Route::get('/practices/draft', fn () => app(CabinetController::class)->empty('Черновики практик'))->name('cabinet.practices.draft');
Route::get('/initiatives', [CabinetController::class, 'initiatives'])->name('cabinet.initiatives');
Route::get('/initiatives/draft', fn () => app(CabinetController::class)->empty('Черновики инициатив'))->name('cabinet.initiatives.draft');
Route::get('/lessons', [CabinetController::class, 'lessons'])->name('cabinet.lessons');
Route::get('/lessons/{lesson}', [CabinetController::class, 'lesson'])->whereNumber('lesson')->name('cabinet.lesson');
Route::get('/statistics/{section}', [CabinetController::class, 'statistics'])->name('cabinet.statistics');

// Разделы, которые в копию не переносились, — на боевой сайт
foreach (['login', 'register'] as $uri) {
    Route::redirect('/'.$uri, config('kinouroki.prod_url').'/'.$uri);
}
