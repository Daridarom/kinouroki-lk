<?php

namespace App\Http\Controllers;

use App\Models\Film;

/**
 * Страницы кабинета, где на проде личные данные педагогов и учеников
 * (профиль, социальные практики, инициативы, статистика). Разметка снята
 * с lk.kinouroki.org 24.09.2026, а данные ЗАМЕНЕНЫ примерами —
 * настоящих имён, школ и фото детей в копии нет.
 */
class CabinetController extends Controller
{
    /** Примеры практик: разной длины, чтобы ловить переполнение и переносы. */
    private function samplePractices(): array
    {
        $films = Film::query()->latest('published_at')->limit(6)->get();
        $samples = [
            ['Добрые крышечки', 'МБОУ СОШ № 1 г. Пример', 'Пример-на-Волге, Примерская область', '5 «А»', 'Иванова М. П.', 11.5, 12, true],
            ['Письмо солдату: как наш класс поддержал героев и что мы поняли о мужестве и чести за этот учебный год', 'Государственное бюджетное общеобразовательное учреждение средняя общеобразовательная школа № 1234 с углублённым изучением английского языка', 'Санкт-Примербург', '8 «Б»', 'Константинопольская-Преображенская А. В.', 16, 12, false],
            ['Кормушки', 'МАОУ Гимназия № 7', 'пос. Примерный', '2 «В»', 'Петров С. С.', 6, 7, true],
            ['Спектакль для малышей', 'МБОУ ООШ с. Примерное', 'с. Примерное', '6 «А»', 'Сидорова Е. А.', 9, 10, false],
            ['Сад памяти', 'МКОУ СОШ № 3', 'г. Пример', '10 «А»', 'Кузнецова О. Н.', 14, 13, true],
            ['Бусы для бабушек', 'МБОУ СОШ № 15', 'г. Образцово', '3 «Г»', 'Смирнова Т. И.', 12, 12, true],
        ];

        return array_map(fn ($s, $i) => [
            'id' => 1000 + $i, 'title' => $s[0], 'school' => $s[1], 'city' => $s[2], 'class' => $s[3],
            'author' => $s[4], 'self' => $s[5], 'expert' => $s[6], 'moderated' => $s[7],
            'film' => $films[$i] ?? null, 'date' => now()->subDays($i * 3)->translatedFormat('d F Y'),
        ], $samples, array_keys($samples));
    }

    public function profile()
    {
        return view('cabinet.profile', ['news' => \App\Models\News::limit(3)->get()]);
    }

    public function practices()
    {
        return view('cabinet.practices', [
            'title' => 'Социальные практики', 'total' => 93421, 'practices' => $this->samplePractices(),
            'films' => Film::orderBy('title')->get(), 'withFilter' => true, 'cols' => 2,
        ]);
    }

    public function initiatives()
    {
        return view('cabinet.practices', [
            'title' => 'Инициативы', 'total' => null, 'practices' => array_slice($this->samplePractices(), 0, 3),
            'films' => collect(), 'withFilter' => false, 'cols' => 3,
        ]);
    }

    /** Пустые списки: на проде — только заголовок с «0» и пустота. */
    public function empty(string $title)
    {
        return view('cabinet.practices', [
            'title' => $title, 'total' => 0, 'practices' => [], 'films' => collect(), 'withFilter' => false, 'cols' => 3,
        ]);
    }

    public function lessons()
    {
        return view('cabinet.lessons', ['films' => Film::whereNotNull('lesson_id')->latest('published_at')->get()]);
    }

    public function lesson(int $lesson)
    {
        $film = Film::where('lesson_id', $lesson)->firstOrFail();

        return view('cabinet.lesson', compact('film'));
    }

    public function statistics(string $section = 'report')
    {
        return view('cabinet.statistics', compact('section'));
    }
}
