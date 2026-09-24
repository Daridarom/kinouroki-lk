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
        $encoded = implode('/', array_map('rawurlencode', explode('/', $path)));

        return config('kinouroki.prod_url').$encoded;
    }

    public function posterUrl(): ?string
    {
        return self::prod($this->poster);
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
