{{--
    Каркас кабинета — копия разметки lk.kinouroki.org (авторизованный вид, 24.09.2026).
    Стили и скрипты — БОЕВЫЕ файлы (config/kinouroki.php), поверх них одним файлом
    подключены улучшения: css/kinouroki-adaptive.css и js/kinouroki-adaptive.js.
    Всё, что отличается от прода, помечено комментарием KINOUROKI-ADAPTIVE.
--}}
@php($prod = config('kinouroki.prod_url'))
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <meta name="robots" content="noindex">
    <title>Киноуроки | @yield('title', 'Фильмы')</title>
    <link href="{{ $prod }}{{ config('kinouroki.app_css') }}" rel="stylesheet">
    <style>
        .text-red { color: red !important; }
        @font-face { font-family: "Rubik-Light"; src: url('{{ $prod }}/fonts/Rubik-Light.ttf'); font-weight: 300; }
        .Rubik-Light { font-family: Rubik-Light !important; }
    </style>
    {{-- KINOUROKI-ADAPTIVE: единственный новый CSS-файл --}}
    <link href="{{ asset('css/kinouroki-adaptive.css') }}?v={{ filemtime(public_path('css/kinouroki-adaptive.css')) }}" rel="stylesheet">
    <link href="{{ $prod }}/favicon.ico" rel="shortcut icon" type="image/x-icon">
</head>
<body class="@yield('body_class', 'films.index-section') d-flex flex-column min-vh-100">

<div class="ka-demo-bar">Копия кабинета для проверки улучшений · данные с lk.kinouroki.org на 24.09.2026 · вход и сохранение не работают</div>

<nav id="top-navbar" class=" navbar navbar-expand navbar-dark shadow-sm border-bottom border-dark px-3">
    <div class="d-flex align-items-center justify-content-between w-100" style="min-width:0">
        {{-- На проде здесь цветная SVG-иконка; в копии — упрощённая. aria-* добавлены (KINOUROKI-ADAPTIVE) --}}
        <button class="me-3" id="collapse-side-menu-btn" style="flex-shrink: 0" aria-controls="side-menu" aria-expanded="false" aria-label="Меню">
            <svg width="20" height="20" viewBox="0 0 448 512" aria-hidden="true"><path fill="#1f3eeb" d="M16 132h416c8.837 0 16-7.163 16-16V76c0-8.837-7.163-16-16-16H16C7.163 60 0 67.163 0 76v40c0 8.837 7.163 16 16 16zm0 160h416c8.837 0 16-7.163 16-16v-40c0-8.837-7.163-16-16-16H16c-8.837 0-16 7.163-16 16v40c0 8.837 7.163 16 16 16zm0 160h416c8.837 0 16-7.163 16-16v-40c0-8.837-7.163-16-16-16H16c-8.837 0-16 7.163-16 16v40c0 8.837 7.163 16 16 16z"/></svg>
        </button>
        <ul class="navbar-nav d-flex" style="flex-shrink: 0">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: #fff">
                    <span class="ka-avatar me-2" aria-hidden="true">П</span><span class="d-none d-md-inline-block">{{ config('kinouroki.demo_user.name') }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-light dropdown-menu-end shadow">
                    <li><a class="dropdown-item" href="{{ route('cabinet.profile') }}">Личный кабинет</a></li>
                    <li><a class="dropdown-item border-top mt-2 pt-2" href="{{ route('cabinet.practices.user') }}">Мои социальные практики</a></li>
                    <li><a class="dropdown-item" href="{{ route('cabinet.practices.draft') }}">Черновики практик</a></li>
                    <li><a class="dropdown-item" href="{{ route('cabinet.initiatives.draft') }}">Черновики инициатив</a></li>
                </ul>
            </li>
        </ul>
    </div>
</nav>

<main class=" main_auth ">
    <div class="d-flex">
        @include('partials.side-menu')
        <div class="container p-3">
            @yield('content')
        </div>
    </div>
</main>

<script src="{{ $prod }}{{ config('kinouroki.app_js') }}"></script>
{{-- KINOUROKI-ADAPTIVE --}}
<script src="{{ asset('js/kinouroki-adaptive.js') }}?v={{ filemtime(public_path('js/kinouroki-adaptive.js')) }}"></script>
@stack('scripts')
</body>
</html>
