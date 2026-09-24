<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * Автотесты копии ЛК «Киноуроки».
 * Проверяют, что наши правки (KA-*) ничего не ломают перед передачей на прод.
 * Запуск:  ./vendor/bin/phpunit --testdox   (или php artisan test при установленном nunomaduro/collision)
 */
class KinourokiCopyTest extends TestCase
{
    /** Своя база в памяти для каждого теста: схема + данные копии (без Mockery). */
    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);
    }

    /** Все основные страницы открываются без ошибок. */
    public function test_pages_open(): void
    {
        $urls = [
            '/films', '/films/72', '/films/category/1',
            '/news', '/about', '/faq', '/documents', '/research',
            '/webinars', '/video-programms', '/jurnals', '/contacts',
            '/users/0', '/practies', '/practies/user/0', '/practices/draft',
            '/initiatives', '/initiatives/draft', '/lessons', '/lessons/67',
            '/statistics/report', '/statistics/classes',
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }
    }

    /** [KA-S02] Заголовки безопасности отдаются на каждой странице. */
    public function test_security_headers_present(): void
    {
        $r = $this->get('/films');
        $r->assertHeader('X-Content-Type-Options', 'nosniff');
        $r->assertHeader('Referrer-Policy');
        $r->assertHeader('Permissions-Policy');
        $this->assertTrue(
            $r->headers->has('Content-Security-Policy')
            || $r->headers->has('Content-Security-Policy-Report-Only'),
            'Нет заголовка Content-Security-Policy'
        );
    }

    /** [KA-S03] Очистка HTML вырезает опасное и оставляет обычный текст. */
    public function test_safe_html_strips_dangerous_markup(): void
    {
        $dirty = '<p style="font-size:14pt">Текст</p>'
            .'<script>alert(1)</script>'
            .'<img src="x" onerror="alert(2)">'
            .'<a href="javascript:alert(3)">ссылка</a>';

        $clean = \App\Support\SafeHtml::clean($dirty);

        $this->assertStringContainsString('Текст', $clean);
        $this->assertStringNotContainsStringIgnoringCase('<script', $clean);
        $this->assertStringNotContainsStringIgnoringCase('onerror', $clean);
        $this->assertStringNotContainsStringIgnoringCase('javascript:', $clean);
    }

    /** [KA-032] Поиск практик не ломается от кавычек и регистра. */
    public function test_practice_search_normalizes_query(): void
    {
        $norm = \App\Http\Controllers\CabinetController::class;
        $this->assertSame(
            $norm::normalize('Мечта'),
            $norm::normalize('"МЕЧТА"')
        );
        $this->assertSame($norm::normalize('ёлка'), $norm::normalize('елка'));
    }

    /** Защита данных: в копии нет реальных личных сведений педагогов. */
    public function test_no_real_personal_data_on_practices(): void
    {
        $html = $this->get('/practies')->getContent();
        $this->assertStringContainsString('(пример)', $html);
        // в карточках практик не должно быть e-mail и телефонов
        // (общий адрес проекта info@kinouroki.ru в меню — не личные данные, его не считаем)
        $cards = strip_tags(str_replace('info@kinouroki.ru', '', $html));
        $this->assertDoesNotMatchRegularExpression('/[\w.+-]+@[\w-]+\.[a-z]{2,}/iu', $cards);
        $this->assertDoesNotMatchRegularExpression('/(\+7|8)[\s(-]*9\d{2}[\s)-]*\d{3}[\s-]*\d{2}[\s-]*\d{2}/u', $cards);
    }

    /** Защита данных: e-mail и телефон в профиле скрыты. */
    public function test_profile_hides_contacts(): void
    {
        $html = $this->get('/users/0')->getContent();
        $this->assertStringContainsString('Скрыт', $html);
    }
}
