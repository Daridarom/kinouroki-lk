<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * KINOUROKI-ADAPTIVE [KA-S02] Заголовки безопасности.
 *
 * На проде (24.09.2026) уже есть: HSTS, X-Frame-Options: DENY, X-Content-Type-Options: nosniff.
 * Нет: Content-Security-Policy, Referrer-Policy, Permissions-Policy.
 *
 * Перенос: скопировать файл в app/Http/Middleware/ и подключить в группу web
 * (Laravel 11: bootstrap/app.php → $middleware->web(append: [...]);
 *  Laravel 8–10: app/Http/Kernel.php → $middlewareGroups['web'][] = ...).
 *
 * CSP сначала включать в режиме «только отчёт» (KINOUROKI_CSP_REPORT_ONLY=true):
 * так ничего не сломается, а в консоли браузера будет видно, что ещё нужно разрешить.
 */
class SecurityHeaders
{
    /** Внешние источники, которые реально использует прод (см. SECURITY.md). */
    private const CSP = [
        "default-src 'self'",
        // inline-скрипты на проде есть (Метрика, модалки) → 'unsafe-inline' временно; цель — nonce
        "script-src 'self' 'unsafe-inline' https://mc.yandex.ru https://code.jivosite.com https://use.fontawesome.com https://lk.kinouroki.org",
        "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://use.fontawesome.com https://lk.kinouroki.org",
        "font-src 'self' data: https://fonts.gstatic.com https://use.fontawesome.com https://lk.kinouroki.org",
        "img-src 'self' data: blob: https: ",
        "media-src 'self' https://lk.kinouroki.org",
        "frame-src https://rutube.ru https://www.youtube.com https://*.jivosite.com",
        "connect-src 'self' https://mc.yandex.ru https://*.jivosite.com wss://*.jivosite.com",
        "object-src 'none'",
        "base-uri 'self'",
        "form-action 'self'",
        "frame-ancestors 'none'",
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $header = env('KINOUROKI_CSP_REPORT_ONLY', true)
            ? 'Content-Security-Policy-Report-Only'
            : 'Content-Security-Policy';

        $response->headers->set($header, implode('; ', self::CSP));
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->remove('X-Powered-By');

        return $response;
    }
}
