<?php

namespace Database\Seeders;

use App\Models\Film;
use App\Models\FilmCategory;
use App\Models\News;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Наполняет копию данными, снятыми с публичных страниц lk.kinouroki.org
 * (database/data/*.json, 24.09.2026). Описания сокращены.
 * HTML описания собирается так же, как его хранит прод (один HTML-блок из
 * TinyMCE с инлайн-стилями) — чтобы проблемы вёрстки воспроизводились честно.
 */
class DatabaseSeeder extends Seeder
{
    private const MONTHS = ['января' => 1, 'февраля' => 2, 'марта' => 3, 'апреля' => 4, 'мая' => 5, 'июня' => 6,
        'июля' => 7, 'августа' => 8, 'сентября' => 9, 'октября' => 10, 'ноября' => 11, 'декабря' => 12];

    private const COMMON_LINKS = [
        ['Рекомендации по публикации социальной практики', 'https://disk.yandex.ru/i/zL5ztN7NIq-CkQ'],
        ['Программа воспитания «Киноуроки в школах России»', 'https://disk.yandex.ru/i/ooUJuKdwzCIWzw'],
    ];

    public function run(): void
    {
        foreach ([1 => 'Начальная школа', 2 => 'Основная школа', 3 => 'Средняя школа'] as $id => $name) {
            FilmCategory::create(['id' => $id, 'name' => $name]);
        }

        $films = json_decode(file_get_contents(database_path('data/films.json')), true);
        $slugs = ['Отыщи моё сердце' => '57'];
        foreach ($films as $i => $f) {
            $film = Film::create([
                'slug' => $slugs[$f['id']] ?? $f['id'],
                'title' => $f['t'],
                'quality' => trim($f['q']),
                'description' => $this->description($f),
                'poster' => str_starts_with($f['p'], '/') ? $f['p'] : '/storage/films/poster/'.$f['p'],
                'triller' => $this->media($f['tz'] ?? null),
                'video' => $this->media($f['v'] ?? null),
                'photos' => array_map(fn ($p) => str_starts_with($p, '/') ? $p : '/storage/films/photos/'.$p, $f['ph'] ?? []),
                'links' => $this->links($f),
                'time' => $f['cr'] ?? null,
                'lesson_id' => $f['ls'] ?? null,
                'published_at' => $this->date($f['d'] ?? '', $i),
            ]);
            $film->categories()->sync($f['cats'] ?? []);
        }

        foreach (json_decode(file_get_contents(database_path('data/news.json')), true) as $n) {
            News::create([
                'slug' => $n['slug'], 'title' => $n['title'], 'image' => $n['img'], 'body' => $n['body'],
                'categories' => $n['cats'], 'links' => $n['links'], 'film_slug' => $n['film'] ?? null,
                'published_label' => $n['date'],
            ]);
        }
    }

    private function description(array $f): string
    {
        $p = fn ($text, $style = 'text-align: justify;') => '<p style="'.$style.'"><span style="font-size: 14pt;">'
            .nl2br(e($text), false).'</span></p>';
        $html = '';
        if (! empty($f['a'])) {
            $html .= $p($f['a'], '');
        }
        foreach (preg_split('/\n\n/', $f['ab'] ?? '') as $para) {
            if (trim($para) !== '') {
                $html .= $p(trim($para));
            }
        }
        $html .= '<h3><span style="font-size: 14pt;">Для проведения киноурока мы предлагаем следующие материалы:</span></h3><div class="my-3">';
        $html .= $p('Рассматриваемое понятие: '.mb_strtolower(trim($f['q'])), 'font-size: 16px;');
        if (! empty($f['df'])) {
            $html .= $p('Определение: '.$f['df']);
        }
        if (! empty($f['an'])) {
            $html .= $p('Антипод: '.$f['an'], 'font-size: 16px;');
        }
        if (! empty($f['qt'])) {
            $html .= $p('Цитата:', 'font-size: 16px;').$p($f['qt']);
        }

        return $html.'</div>';
    }

    private function links(array $f): array
    {
        $out = [];
        foreach ($f['l'] ?? [] as $l) {
            $out[] = is_string($l) ? ['Скачать фильм и методическое пособие', 'https://'.$l] : [$l[0], 'https://'.$l[1]];
        }
        foreach (self::COMMON_LINKS as $c) {
            if (! collect($out)->contains(fn ($x) => str_starts_with($x[0], mb_substr($c[0], 0, 20)))) {
                $out[] = $c;
            }
        }

        return $out;
    }

    private function media(?string $v): ?string
    {
        if (! $v) {
            return null;
        }
        [$kind, $rest] = explode(':', $v, 2);

        return match ($kind) {
            'r' => 'https://rutube.ru/play/embed/'.preg_replace('#^([^/?]+)/?\??#', '$1/?', $rest),
            'y' => 'https://www.youtube.com/embed/'.$rest,
            'f' => '/storage/films/triller/'.$rest,
            default => $v,
        };
    }

    private function date(string $d, int $order): Carbon
    {
        if (preg_match('/(\d{1,2})\s+(\S+)\s+(\d{4})/u', $d, $m) && isset(self::MONTHS[$m[2]])) {
            return Carbon::create((int) $m[3], self::MONTHS[$m[2]], (int) $m[1], 12)->subMinutes($order);
        }

        return now()->subDays($order);
    }
}
