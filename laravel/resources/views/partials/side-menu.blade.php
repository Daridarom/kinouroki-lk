{{-- Боковое меню кабинета — копия прода. Активный пункт через request()->routeIs(). --}}
@php($prod = config('kinouroki.prod_url'))
<div class="col-3 side-menu shadow-sm border-right" id="side-menu">
    <div class="mb-3">
        <center>
            <a class="navbar-brand d-none d-md-block" href="{{ route('films.index') }}" style="margin-bottom: 25px">
                <img src="{{ $prod }}/images/backgrounds/logo_w.png" width="35%" alt="Киноуроки">
            </a>
        </center>
        <button class="btn btn-light text-muted w-100 py-2 fw-light" id="show-search" type="button">
            <span class="me-2"> Универсальный поиск </span>
        </button>
    </div>
    <li class="nav-item {{ request()->routeIs('news.*') ? 'active' : '' }}"><a class="nav-link align-items-center d-flex" href="{{ route('news.index') }}">Новости</a></li>
    <li class="nav-item "><a class="nav-link" href="{{ $prod }}/users">Личный кабинет</a></li>
    <li class="nav-item "><a class="nav-link align-items-center d-flex" href="{{ $prod }}/practies">Социальные практики</a></li>
    <li class="nav-item "><a class="nav-link align-items-center d-flex" href="{{ $prod }}/initiatives"> Инициативы </a></li>
    <li class="nav-item "><a class="nav-link align-items-center d-flex" href="{{ $prod }}/statistics/activities">Статистика</a></li>
    <li class="nav-item {{ request()->routeIs('films.*') ? 'active' : '' }}">
        <a class="nav-link align-items-center d-flex" href="{{ route('films.index') }}">Киноуроки</a>
        @if(request()->routeIs('films.*'))
        <div class="list-group my-3 shadow">
            @php($cat = request()->route('category'))
            <a class="list-group-item list-group-item-action list-group-item-primary {{ request()->routeIs('films.index') ? 'active' : '' }}" href="{{ route('films.index') }}"> Все киноуроки </a>
            @foreach(\App\Models\FilmCategory::all() as $c)
                <a class="list-group-item list-group-item-action list-group-item-primary {{ $cat && $cat->id === $c->id ? 'active' : '' }}" href="{{ route('films.category', $c) }}"> {{ $c->name }} </a>
            @endforeach
        </div>
        @endif
    </li>
    <li class="nav-item {{ request()->routeIs('pages.journals') ? 'active' : '' }}"><a class="nav-link align-items-center d-flex" href="{{ route('pages.journals') }}">Журналы</a></li>
    <li class="nav-item {{ request()->routeIs('pages.video') ? 'active' : '' }}"><a class="nav-link align-items-center d-flex " href="{{ route('pages.video') }}">Видеопрограммы </a></li>
    <li class="nav-item {{ request()->routeIs('pages.webinars') ? 'active' : '' }}"><a class="nav-link" href="{{ route('pages.webinars') }}">Вебинары</a></li>
    <li class="nav-item {{ request()->routeIs('pages.faq') ? 'active' : '' }}"><a class="nav-link align-items-center d-flex" href="{{ route('pages.faq') }}">Вопросы и ответы</a></li>
    <li class="nav-item {{ request()->routeIs('pages.documents') ? 'active' : '' }}"><a class="nav-link align-items-center d-flex" href="{{ route('pages.documents') }}">Документы</a></li>
    <li class="nav-item {{ request()->routeIs('pages.about') ? 'active' : '' }}"><a class="nav-link" href="{{ route('pages.about') }}">О проекте Киноуроки</a></li>
    <li class="nav-item {{ request()->routeIs('pages.research') ? 'active' : '' }}"><a class="nav-link" href="{{ route('pages.research') }}">Исследование</a></li>
    <div class="footer_component text-center">
        <div class="row g-0 lead align-items-center justify-content-center mb-4">
            <div class="text-white font-size-14">Телефон: <a class="text-white fw-light text-decoration-none px-0 text-dark" href="tel:88004445458">8 800 444 54 58</a></div>
            <div class="text-white font-size-14 mt-3">E-Mail: <a class="text-white fw-light text-decoration-none px-0 text-dark" href="mailto:info@kinouroki.ru">info@kinouroki.ru </a></div>
        </div>
        <div class="row row-cols-auto g-0 social-links align-items-center justify-content-center bg-white rounded shadow py-2">
            <a class="social-link" href="https://t.me/kinouroki" target="_blank" aria-label="Telegram"><svg aria-hidden="true" class="svg-inline--fa fa-telegram fa-w-16" focusable="false" height="28px" role="img" viewBox="0 0 496 512" width="28px"><path fill="currentColor" d="M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8zm121.8 169.9l-40.7 191.8c-3 13.6-11.1 16.9-22.4 10.5l-62-45.7-29.9 28.8c-3.3 3.3-6.1 6.1-12.5 6.1l4.4-63.1 114.9-103.8c5-4.4-1.1-6.9-7.7-2.5l-142 89.4-61.2-19.1c-13.3-4.2-13.6-13.3 2.8-19.7l239.1-92.2c11.1-4 20.8 2.7 17.2 19.5z"/></svg></a>
            <a class="social-link" href="https://vk.com/kinouroki" target="_blank" aria-label="ВКонтакте"><svg aria-hidden="true" class="vk" focusable="false" height="28px" role="img" viewBox="0 0 576 512" width="28px"><path d="M545 117.7c3.7-12.5 0-21.7-17.8-21.7h-58.9c-15 0-21.9 7.9-25.6 16.7 0 0-30 73.1-72.4 120.5-13.7 13.7-20 18.1-27.5 18.1-3.7 0-9.4-4.4-9.4-16.9V117.7c0-15-4.2-21.7-16.6-21.7h-92.6c-9.4 0-15 7-15 13.5 0 14.2 21.2 17.5 23.4 57.5v86.8c0 19-3.4 22.5-10.9 22.5-20 0-68.6-73.4-97.4-157.4-5.8-16.3-11.5-22.9-26.6-22.9H38.8c-16.8 0-20.2 7.9-20.2 16.7 0 15.6 20 93.1 93.1 195.5C160.4 378.1 229 416 291.4 416c37.5 0 42.1-8.4 42.1-22.9 0-66.8-3.4-73.1 15.4-73.1 8.7 0 23.7 4.4 58.7 38.1 40 40 46.6 57.9 69 57.9h58.9c16.8 0 25.3-8.4 20.4-25-11.2-34.9-86.9-106.7-90.3-111.5-8.7-11.2-6.2-16.2 0-26.2.1-.1 72-101.3 79.4-135.6z"/></svg></a>
        </div>
        <div class="small fw-light text-light font-size-12 mt-4">Платформа разработана и поддерживается участниками проекта <br> «Киноуроки в школах мира» <br> 2020 - 2026 </div>
    </div>
</div>
