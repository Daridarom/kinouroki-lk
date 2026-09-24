<?php

namespace App\Http\Controllers;

/**
 * Статические разделы (О проекте, FAQ, Документы…). На проде они в своих
 * контроллерах и таблицах; в копии — из database/data/pages.json, чтобы
 * было на чём проверять адаптивность.
 */
class PageController extends Controller
{
    public function show(string $key)
    {
        $pages = json_decode(file_get_contents(database_path('data/pages.json')), true);
        abort_unless(isset($pages[$key]), 404);

        return view('pages.show', ['page' => $pages[$key], 'key' => $key]);
    }
}
