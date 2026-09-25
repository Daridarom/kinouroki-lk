<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Film extends Model
{
    protected $guarded = [];

    protected $casts = [
        'photos' => 'array',
        'links' => 'array',
        'published_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(FilmCategory::class, 'category_film');
    }

    /** Абсолютный URL файла с боевого сайта (постеры, фото, трейлеры). */
    public static function prod(?string $path): ?string
    {
        if (! $path) {
            return null;
        }
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }
        $encoded = '/'.ltrim(implode('/', array_map('rawurlencode', explode('/', $path))), '/');

        return config('kinouroki.prod_url').$encoded;
    }

    public function posterUrl(): ?string
    {
        return self::prod($this->poster);
    }

    /**
     * KINOUROKI-ADAPTIVE [KA-053] Лёгкие превью постера для карточек: 480×270 и 960×540 (для чётких экранов), WebP.
     * Было: оригиналы PNG/BMP 0,3–1,9 МБ (67 постеров = 35 МБ). Стало: 0,8 МБ (или 1,7 МБ на экранах с высокой плотностью).
     * Превью создаёт `php artisan kinouroki:poster-thumbs` в storage/app/public/thumbs/<путь постера>.webp и …@2x.webp.
     * В копии превью уже лежат в public/img/thumbs/. Если превью нет — отдаём оригинал, ничего не ломается.
     *
     * @return array{0: ?string, 1: ?string} [превью 1x, превью 2x]
     */
    public function posterThumbs(): array
    {
        $rel = self::storageRelative($this->poster);
        if ($rel !== null) {
            $base = preg_replace('/\.[^.\/]+$/', '', $rel);
            foreach ([['app/public/thumbs/', 'storage/thumbs/', 'storage_path'], ['img/thumbs/', 'img/thumbs/', 'public_path']] as [$dir, $url, $fn]) {
                if (is_file($fn($dir.$base.'.webp'))) {
                    $enc = fn ($p) => asset($url.implode('/', array_map('rawurlencode', explode('/', $p))));
                    $x2 = is_file($fn($dir.$base.'@2x.webp')) ? $enc($base.'@2x.webp') : null;

                    return [$enc($base.'.webp'), $x2];
                }
            }
        }

        return [$this->posterUrl(), null];
    }

    public function posterThumbUrl(): ?string
    {
        return $this->posterThumbs()[0];
    }

    /** «https://lk…/storage/films/poster/a/b.png», «/storage/films/…», «films/…» → «films/poster/a/b.png» */
    private static function storageRelative(?string $path): ?string
    {
        if (! $path) {
            return null;
        }
        $p = rawurldecode(preg_replace('#^https?://[^/]+#', '', $path));
        $p = ltrim(preg_replace('#^/?storage/#', '', $p), '/');

        return str_starts_with($p, 'films/') ? $p : null;
    }

    public function trillerIsFile(): bool
    {
        return $this->triller && Str::endsWith(Str::lower($this->triller), '.mp4');
    }

    /** Первый абзац описания без HTML — анонс для карточки. */
    public function lead(int $limit = 160): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags(str_replace(['<br>', '</p>'], ' ', (string) $this->description))));

        return Str::limit($text, $limit);
    }

    /* ---------- KINOUROKI-ADAPTIVE: фильтры каталога ---------- */

    public function scopeFilter(Builder $q, array $f): Builder
    {
        if ($s = trim($f['q'] ?? '')) {
            $q->where(fn ($w) => $w->where('title', 'like', "%{$s}%")
                ->orWhere('quality', 'like', "%{$s}%")
                ->orWhere('description', 'like', "%{$s}%"));
        }
        if ($quality = $f['quality'] ?? null) {
            $q->where('quality', $quality);
        }
        if ($cat = $f['category'] ?? null) {
            $q->whereHas('categories', fn ($w) => $w->whereKey($cat));
        }

        return match ($f['sort'] ?? 'new') {
            'old' => $q->orderBy('published_at'),
            'az' => $q->orderBy('title'),
            default => $q->orderByDesc('published_at')->orderByDesc('id'),
        };
    }
}
