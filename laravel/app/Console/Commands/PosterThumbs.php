<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * KINOUROKI-ADAPTIVE [KA-053] Уменьшенные превью постеров фильмов.
 *
 * Проблема прода (замер 25.09.2026): постеры — PNG/BMP по 0,3–1,9 МБ, на странице /films 67 штук = 35 МБ.
 * На телефоне превью долго серые. Команда делает рядом копии 480×270 в WebP (~20–40 КБ каждая),
 * оригиналы не трогает. Повторный запуск пересобирает только изменившиеся постеры.
 *
 * Запуск на сервере:  php artisan kinouroki:poster-thumbs
 * Нужно: расширение GD с поддержкой WebP (php -r 'var_dump(function_exists("imagewebp"));').
 * Можно поставить в планировщик раз в сутки или вызывать после загрузки постера в админке.
 */
class PosterThumbs extends Command
{
    protected $signature = 'kinouroki:poster-thumbs
        {--src=films/poster : Папка постеров внутри storage/app/public}
        {--width=480} {--height=270} {--quality=78}
        {--force : Пересобрать все, даже неизменившиеся}';

    protected $description = 'Создаёт лёгкие WebP-превью постеров фильмов (оригиналы не изменяются)';

    public const THUMB_DIR = 'films/poster-thumbs';

    public function handle(): int
    {
        if (! function_exists('imagewebp')) {
            $this->error('В PHP нет GD с поддержкой WebP.');
            return self::FAILURE;
        }

        $root = storage_path('app/public');
        $src = $root.'/'.trim($this->option('src'), '/');
        if (! is_dir($src)) {
            $this->error("Нет папки {$src}");
            return self::FAILURE;
        }

        [$w, $h, $q] = [(int) $this->option('width'), (int) $this->option('height'), (int) $this->option('quality')];
        $made = $skipped = $failed = 0;
        $before = $after = 0;

        foreach (File::allFiles($src) as $file) {
            $ext = strtolower($file->getExtension());
            if (! in_array($ext, ['png', 'jpg', 'jpeg', 'bmp', 'webp', 'gif'], true)) {
                continue;
            }
            $rel = ltrim(str_replace($src, '', $file->getPathname()), '/');
            $out = $root.'/'.self::THUMB_DIR.'/'.preg_replace('/\.[^.]+$/', '', $rel).'.webp';

            if (! $this->option('force') && is_file($out) && filemtime($out) >= $file->getMTime()) {
                $skipped++;
                continue;
            }

            $img = self::open($file->getPathname(), $ext);
            if (! $img) {
                $this->warn("Не удалось открыть: {$rel}");
                $failed++;
                continue;
            }

            $thumb = self::cover($img, $w, $h);
            File::ensureDirectoryExists(dirname($out));
            imagewebp($thumb, $out, $q);
            imagedestroy($img);
            imagedestroy($thumb);

            $before += $file->getSize();
            $after += filesize($out);
            $made++;
        }

        $this->info(sprintf(
            'Готово: создано %d, без изменений %d, ошибок %d. Было %.1f МБ → стало %.1f МБ.',
            $made, $skipped, $failed, $before / 1e6, $after / 1e6
        ));

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    private static function open(string $path, string $ext): \GdImage|false
    {
        return match ($ext) {
            'png' => @imagecreatefrompng($path),
            'jpg', 'jpeg' => @imagecreatefromjpeg($path),
            'bmp' => @imagecreatefrombmp($path),
            'webp' => @imagecreatefromwebp($path),
            'gif' => @imagecreatefromgif($path),
            default => false,
        };
    }

    /** Обрезка «по центру» под пропорцию 16:9 без искажений (как object-fit: cover). */
    private static function cover(\GdImage $img, int $w, int $h): \GdImage
    {
        $sw = imagesx($img);
        $sh = imagesy($img);
        $scale = max($w / $sw, $h / $sh);
        $cw = (int) round($w / $scale);
        $ch = (int) round($h / $scale);
        $sx = (int) (($sw - $cw) / 2);
        $sy = (int) (($sh - $ch) / 2);

        $dst = imagecreatetruecolor($w, $h);
        imagefill($dst, 0, 0, imagecolorallocate($dst, 233, 236, 239));
        imagecopyresampled($dst, $img, 0, 0, $sx, $sy, $w, $h, $cw, $ch);

        return $dst;
    }
}
