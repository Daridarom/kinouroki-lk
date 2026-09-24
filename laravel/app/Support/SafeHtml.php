<?php

namespace App\Support;

/**
 * KINOUROKI-ADAPTIVE [KA-S03] Очистка HTML из визуального редактора (TinyMCE) перед сохранением.
 *
 * На проде описания фильмов, киноуроков, новостей и, вероятно, социальных практик выводятся
 * как {!! $html !!} — сырым HTML. Если текст может написать не только администратор
 * (педагоги публикуют практики), это прямой путь к XSS, а сессионная кука на проде
 * НЕ HttpOnly (см. SECURITY.md, S-01) — украденная сессия = чужой аккаунт.
 *
 * Лучший вариант для прода: пакет mews/purifier (HTML Purifier):
 *     composer require mews/purifier
 *     $model->description = clean($request->input('description'));
 * Этот класс — лёгкая замена без зависимостей: белый список тегов и атрибутов,
 * заодно убирает мусорные инлайн-стили из Word (font-family, font-size, justify…),
 * из-за которых текст выглядит по-разному (AUDIT.md, п. 7).
 */
class SafeHtml
{
    private const TAGS = ['p', 'br', 'b', 'strong', 'i', 'em', 'u', 'ul', 'ol', 'li', 'h3', 'h4', 'blockquote', 'a', 'span', 'div'];

    private const ATTRS = ['a' => ['href', 'target', 'rel', 'class']];

    public static function clean(?string $html): string
    {
        if (! $html) {
            return '';
        }
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8"?><div id="ka-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        $root = $doc->getElementById('ka-root');
        self::walk($root);
        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return trim($out);
    }

    private static function walk(\DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof \DOMElement) {
                $tag = strtolower($child->tagName);
                if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input'], true)) {
                    $node->removeChild($child);

                    continue;
                }
                self::walk($child);
                if (! in_array($tag, self::TAGS, true)) {
                    while ($child->firstChild) {           // неизвестный тег: оставить текст, убрать обёртку
                        $node->insertBefore($child->firstChild, $child);
                    }
                    $node->removeChild($child);

                    continue;
                }
                foreach (iterator_to_array($child->attributes) as $attr) {
                    if (! in_array($attr->name, self::ATTRS[$tag] ?? [], true)) {
                        $child->removeAttribute($attr->name);   // style, on*, class и т. п.
                    }
                }
                if ($tag === 'a') {
                    $href = $child->getAttribute('href');
                    if (! preg_match('#^(https?:|mailto:|/)#i', $href)) {
                        $child->removeAttribute('href');       // javascript:, data: и прочее
                    }
                    if ($child->getAttribute('target') === '_blank') {
                        $child->setAttribute('rel', 'noopener noreferrer');
                    }
                }
            }
        }
    }
}
