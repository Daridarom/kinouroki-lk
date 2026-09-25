{{-- /practies, /initiatives, /practies/user/{id}, /practices/draft
     Разметка карточек — как на проде (снято 24.09.2026). Данные — обезличенные практики (database/data/practices.json).
     Все отличия от прода помечены KINOUROKI-ADAPTIVE [KA-…], полный список — CHANGELOG.md. --}}
@extends('layouts.app')
@section('title', $title)
@section('body_class', 'practies-section')
@php($filters = $filters ?? [])
@section('content')
<div class="row">
    {{-- Прод: order-2 order-lg-0 — на телефоне фильтр ВЫШЕ списка и занимает весь экран. [KA-021] --}}
    <div class="{{ $withFilter ? 'col-md-7 col-lg-8 order-2 order-md-0' : 'col-lg-12' }} practies">
        <h1 class="font-size-28 fw-light text-muted ka-h1"> {{ $title }} @if(!is_null($total))<span class="badge badge-primary ms-3"> {{ $total }} </span>@endif</h1>{{-- [KA-048] --}}

        @if($practices->isEmpty() && !$withFilter)
            {{-- KINOUROKI-ADAPTIVE [KA-023] пустое состояние. Прод: только заголовок и «0» --}}
            <div class="card p-4 mt-3 text-center fw-light ka-empty-state">
                <div class="font-size-20 mb-2">Здесь пока пусто</div>
                <p class="text-muted mb-3">Проведите киноурок и опубликуйте социальную практику класса — она появится в этом списке.</p>
                <a class="btn btn-primary mx-auto" href="{{ route('cabinet.lessons') }}">Выбрать киноурок</a>
            </div>
        @else
        @if($withFilter)
            <div class="small text-muted mt-2" data-ka-pr-found aria-live="polite">В копии: {{ $practices->count() }} практик (обезличенные примеры с первых страниц прода)</div>
        @endif
        {{-- [KA-022] row-cols-sm-2 + g-3: на планшете 2 колонки (прод: 1) --}}
        <div class="row align-items-start row-cols-1 row-cols-sm-2 row-cols-lg-{{ $cols }} mt-2 g-3" data-ka-pr-grid>
            @foreach($practices as $p)
            <div class="col" data-ka-pr
                 data-ka-text="{{ \App\Http\Controllers\CabinetController::normalize($p['title'].' '.$p['film'].' '.$p['quality'].' '.$p['school']) }}"
                 data-ka-film="{{ $p['film'] }}" data-ka-expert="{{ $p['expert'] }}" data-ka-title="{{ \App\Http\Controllers\CabinetController::normalize($p['title']) }}" data-ka-i="{{ $loop->index }}">
                <a class="card text-decoration-none text-dark h-100" href="{{ config('kinouroki.prod_url') }}/practies">
                    {{-- [KA-026] фиксированная пропорция фото: прод — фото любой высоты, карточки «скачут» --}}
                    <img alt="Фото социальной практики (заглушка)" class="card-img-top ka-cover" src="{{ asset('img/placeholder.svg') }}" loading="lazy">
                    <div class="card-body practice-card ">
                        <div class="small text-muted mb-2 d-flex w-100 align-items-center">
                            <div class="me-2"> Социальная практика №{{ $p['id'] }} </div>
                            @if($p['moderated'])
                                <div class="ms-auto" title="Проверено экспертом"><span class="ka-check" aria-label="Проверено экспертом">✓</span></div>
                            @endif
                        </div>
                        {{-- [KA-027] название в 2 строки (прод: обрезка в 1 строку text-truncate) --}}
                        <h4 class="ka-clamp-2 col px-0 font-size-20">{{ $p['title'] }}</h4>
                        <div class="font-size-14 fw-light my-2">
                            {{-- [KA-028] прод пишет «Страна: Россия, -», когда населённый пункт не указан --}}
                            <div> Страна: Россия{{ $p['place'] ? ', '.$p['place'] : '' }} </div>
                            <div class="ka-clamp-2"> Заведение: {{ $p['school'] }} </div>
                            <div> Класс: {{ $p['class'] }} </div>
                        </div>
                        <div class="font-size-14 fw-light mb-2"> Фильм: {{ $p['film'] }} </div>
                        <div><span class="font-size-14 fw-light">Качество: </span><span class="me-2 disabled font-size-14 fw-light"> {{ $p['quality'] }} </span></div>
                        <div class="card-text small text-truncate col px-0"> Автор: <span class="text-muted"> {{ $p['author'] }} </span></div>
                        {{-- [KA-029] оценки: прод красит самоанализ красным ВСЕГДА (класс text-red на каждой карточке) — смысл цвета теряется.
                             Здесь красный только если самооценка выше экспертной больше чем на 3 балла, с подсказкой. --}}
                        @php($gap = $p['self'] - $p['expert'])
                        <div class="card-text small col px-0 d-flex gap-3 flex-wrap">
                            <span>Самоанализ: <span class="{{ $gap > 3 ? 'text-danger' : 'text-muted' }}" @if($gap > 3) title="Самооценка выше экспертной на {{ $gap }}" @endif> {{ rtrim(rtrim(number_format($p['self'], 1, '.', ''), '0'), '.') }} </span></span>
                            <span>Эксперт: <span class="text-muted"> {{ rtrim(rtrim(number_format($p['expert'], 1, '.', ''), '0'), '.') }} </span></span>
                        </div>
                        <div class="card-text"><small class="text-muted"> {{ $p['ago'] }} </small></div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
        @if($withFilter)
            <div class="text-center text-muted py-4" hidden data-ka-pr-empty>
                Ничего не найдено. Попробуйте другое слово — поиск идёт по названию, фильму, качеству и учреждению.
            </div>
            {{-- [KA-030] «Показать ещё» вместо тысяч страниц пагинации на телефоне --}}
            <div class="text-center mt-3"><button type="button" class="btn btn-outline-primary" data-ka-pr-more hidden>Показать ещё</button></div>
        @endif
        @endif
    </div>

    @if($withFilter)
    <div class="col-md-5 col-lg-4 order-1 order-md-0">
        {{-- [KA-021] на телефоне фильтр свёрнут под кнопку --}}
        <details class="ka-filter-details mt-3 mt-md-4" open>
            <summary class="btn btn-outline-primary w-100 d-md-none">Поиск и фильтр</summary>
            <form class="form-group-lg mt-2 mt-md-0 card py-3" method="GET" action="{{ route('cabinet.practices') }}" data-ka-pr-form>
                <div class="px-3">
                    {{-- [KA-031] один общий поиск сверху вместо полей «Номер» и «Название» по отдельности --}}
                    <label class="fw-light text-muted" for="ka-pr-q">Поиск</label>
                    <input type="search" class="form-control mt-1" name="q" id="ka-pr-q" value="{{ $filters['q'] ?? '' }}" placeholder="Название, фильм, качество, школа" autocomplete="off">
                    <div class="mt-2">
                        <label class="visually-hidden" for="ka-pr-sort">Порядок</label>
                        <select class="form-select" name="sort" id="ka-pr-sort">
                            <option value="new" @selected(($filters['sort'] ?? 'new') === 'new')>Сначала новые</option>
                            <option value="old" @selected(($filters['sort'] ?? '') === 'old')>Сначала старые</option>
                            <option value="score" @selected(($filters['sort'] ?? '') === 'score')>По оценке эксперта</option>
                            <option value="title" @selected(($filters['sort'] ?? '') === 'title')>По названию</option>
                        </select>
                    </div>
                    <div class="my-2">
                        <label class="visually-hidden" for="ka-pr-film">Киноурок</label>
                        <select class="form-select ka-select-wrap" name="film" id="ka-pr-film">
                            <option value="">Киноурок: все</option>
                            @foreach($films as $f)<option value="{{ $f->title }}" @selected(($filters['film'] ?? '') === $f->title)>{{ $f->title }} | {{ $f->quality }}</option>@endforeach
                        </select>
                    </div>
                    <div><input type="text" class="form-control mt-2" name="period" id="period" placeholder="Период (на проде — календарь)" disabled></div>
                    <div class="row px-4">
                        {{-- [KA-033] у чекбоксов появились id: клик по подписи ставит галочку (прод: не ставит) --}}
                        @foreach(['photos' => 'Есть фотографии', 'video' => 'Есть видео', 'docs' => 'Есть вложения', 'initiative' => 'Инициатива'] as $name => $label)
                        <div class="form-check mt-2">
                            <input type="checkbox" class="form-check-input" name="{{ $name }}" id="f-{{ $name }}" disabled>
                            <label class="form-check-label" for="f-{{ $name }}">{{ $label }}</label>
                        </div>
                        @endforeach
                    </div>
                    <div class="col d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary flex-grow-1">Применить</button>
                        <a class="btn btn-outline-secondary" href="{{ route('cabinet.practices') }}" data-ka-pr-reset>Сбросить</a>
                    </div>
                    <p class="small text-muted mt-2 mb-0">В копии поиск срабатывает сразу при вводе. Флажки и период на проде работают через сервер — здесь отключены.</p>
                </div>
            </form>
        </details>
    </div>
    @endif
</div>
@endsection
