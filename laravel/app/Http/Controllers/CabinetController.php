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
    /**
     * 96 практик с первых страниц /practies (24.09.2026), ОБЕЗЛИЧЕНЫ при снятии:
     * автор → «Педагог N (пример)», школа → тип + номер-пример, населённый пункт → пример,
     * фото → заглушка. Названия, фильмы, качества, классы и оценки — как на проде:
     * на них проверяем отображение и поиск.
     */
    private function samplePractices(): \Illuminate\Support\Collection
    {
        $films = Film::all()->keyBy('title');

        return collect(json_decode(file_get_contents(database_path('data/practices.json')), true))
            ->map(fn ($p) => $p + ['filmModel' => $films[$p['film']] ?? null]);
    }

    /**
     * KINOUROKI-ADAPTIVE [KA-032] поиск практик.
     * Прод (/practies_search?name=…) ищет ТОЛЬКО по названию, кавычки считает частью слова
     * («"Мечта"» находит 24 вместо 893), не ищет по фильму/качеству/школе, отвечает 3–12 с.
     * Здесь: нормализация запроса (регистр, ё/е, кавычки и знаки), поиск по названию,
     * фильму, качеству и учреждению, каждое слово запроса должно встретиться.
     */
    public static function normalize(string $s): string
    {
        $s = mb_strtolower($s);
        $s = str_replace('ё', 'е', $s);
        $s = preg_replace('/[«»"\'“”„()\[\].,!?:;—–-]+/u', ' ', $s);

        return trim(preg_replace('/\s+/u', ' ', $s));
    }

    private function filterPractices(\Illuminate\Support\Collection $items, array $f): \Illuminate\Support\Collection
    {
        if ($q = self::normalize($f['q'] ?? '')) {
            $words = explode(' ', $q);
            $items = $items->filter(function ($p) use ($words) {
                $hay = self::normalize($p['title'].' '.$p['film'].' '.$p['quality'].' '.$p['school']);
                foreach ($words as $w) {
                    if (! str_contains($hay, $w)) {
                        return false;
                    }
                }

                return true;
            });
        }
        if ($film = $f['film'] ?? null) {
            $items = $items->where('film', $film);
        }

        return match ($f['sort'] ?? 'new') {
            'old' => $items->reverse(),
            'score' => $items->sortByDesc('expert'),
            'title' => $items->sortBy(fn ($p) => self::normalize($p['title'])),
            default => $items,
        };
    }

    public function profile()
    {
        return view('cabinet.profile', ['news' => \App\Models\News::limit(3)->get()]);
    }

    public function practices()
    {
        $filters = request()->only(['q', 'film', 'sort']);

        return view('cabinet.practices', [
            'title' => 'Социальные практики', 'total' => 93421,
            'practices' => $this->filterPractices($this->samplePractices(), $filters)->values(),
            'films' => Film::orderBy('title')->get(), 'withFilter' => true, 'cols' => 2, 'filters' => $filters,
        ]);
    }

    public function initiatives()
    {
        return view('cabinet.practices', [
            'title' => 'Инициативы', 'total' => null, 'practices' => $this->samplePractices()->take(6)->values(),
            'films' => collect(), 'withFilter' => false, 'cols' => 3,
        ]);
    }

    /** Пустые списки: на проде — только заголовок с «0» и пустота. */
    public function empty(string $title)
    {
        return view('cabinet.practices', [
            'title' => $title, 'total' => 0, 'practices' => collect(), 'films' => collect(), 'withFilter' => false, 'cols' => 3,
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
