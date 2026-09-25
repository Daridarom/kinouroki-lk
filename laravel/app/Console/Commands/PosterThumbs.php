<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * KINOUROKI-ADAPTIVE [KA-053] Уменьшенные превью постеров фильмов.
 *
 * Проблема прода (замер 25.09.2026): постеры — PNG/BMP по 0,3–1,9 МБ, на странице /films 67 штук = 35 МБ.
 * На телефоне превью долго серые. Команда делает копии 480×270 и 960×540 (для чётких экранов) в WebP
 * в storage/app/public/thumbs/<путь постера>.webp и …@2x.webp. Оригиналы не трогает.
 * Повторный запуск пересобирает только изменившиеся постеры. Итог на 67 постерах: 35 МБ → 0,8 МБ (1x) / 1,7 МБ (2x).
 *
 * Запуск на сервере:  php artisan kinouroki:poster-thumbs
 * Нужно: расширение GD с поддержкой WebP (php -r 'var_dump(function_exists("imagewebp"));').
 * Можно поставить в планировщик раз в сутки или вызывать после загрузки постера в админке.
 */
class PosterThumbs extends Command
{
    protected $signature = 'kinouroki:poster-thumbs
        {--src=films/poster,films/posters : Папки постеров внутри storage/app/public (через запятую)}
        {--width=480} {--height=270} {--quality=78}
        {--force : Пересобрать все, даже неизменившиеся}';

    protected $description = 'Создаёт лёгкие WebP-превью постеров фильмов (оригиналы не изменяются)';

    public const THUMB_DIR = 'thumbs';

    public function handle(): int
    {
        if (! function_exists('imagewebp')) {
            $this->error('В PHP нет GD с поддержкой WebP.');
            return self::FAILURE;
        }

        $root = storage_path('app/public');
        [$w, $h, $q] = [(int) $this->option('width'), (int) $this->option('height'), (int) $this->option('quality')];
        $made = $skipped = $failed = 0;
        $before = $after = 0;

        foreach (array_filter(array_map('trim', explode(',', $this->option('src')))) as $srcRel) {
            $src = $root.'/'.trim($srcRel, '/');
            if (! is_dir($src)) {
                $this->warn("Нет папки {$src} — пропускаю");
                continue;
            }

            foreach (File::allFiles($src) as $file) {
                $ext = strtolower($file->getExtension());
                if (! in_array($ext, ['png', 'jpg', 'jpeg', 'bmp', 'webp', 'gif'], true)) {
                    continue;
                }
                $rel = ltrim(substr($file->getPathname(), strlen($root)), '/');
                $base = $root.'/'.self::THUMB_DIR.'/'.preg_replace('/\.[^.\/]+$/', '', $rel);
                $targets = [[$base.'.webp', $w, $h], [$base.'@2x.webp', $w * 2, $h * 2]];

                $fresh = ! $this->option('force') && collect($targets)->every(fn ($t) => is_file($t[0]) && filemtime($t[0]) >= $file->getMTime());
                if ($fresh) {
                    $skipped++;
                    continue;
                }

                $img = self::open($file->getPathname(), $ext);
                if (! $img) {
                    $this->warn("Не удалось открыть: {$rel}");
                    $failed++;
                    continue;
                }

                File::ensureDirectoryExists(dirname($base.'.webp'));
                foreach ($targets as [$out, $tw, $th]) {
                    $thumb = self::cover($img, $tw, $th);
                    imagewebp($thumb, $out, $q);
                    imagedestroy($thumb);
                    $after += filesize($out);
                }
                imagedestroy($img);
                $before += $file->getSize();
                $made++;
            }
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
