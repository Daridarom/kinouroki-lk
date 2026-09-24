@extends('layouts.app')
@section('title', $category ? 'Фильмы | '.$category->name : 'Фильмы')

@section('content')
{{-- Заголовок — как на проде --}}
<div class="font-size-28 title fw-light text-muted mb-3">
    {{ $category ? $category->name : 'Фильмы' }} <span class="badge badge-primary ms-3">{{ $films->count() }}</span>
</div>

{{-- KINOUROKI-ADAPTIVE: панель поиска и фильтров. Работает и без JS (обычная GET-форма),
     а js/kinouroki-adaptive.js фильтрует карточки мгновенно, без перезагрузки. --}}
<form method="GET" action="{{ url()->current() }}" class="ka-filters card p-3 mb-3" data-ka-filters>
    <div class="row g-2 align-items-center">
        <div class="col-12 col-lg-6">
            <label class="visually-hidden" for="ka-q">Поиск</label>
            <input id="ka-q" type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control" placeholder="Поиск: название, качество, слово из описания" autocomplete="off">
        </div>
        <div class="col-7 col-lg-4">
            <label class="visually-hidden" for="ka-quality">Качество</label>
            <select id="ka-quality" name="quality" class="form-select">
                <option value="">Все качества ({{ $qualities->count() }})</option>
                @foreach($qualities as $q)
                    <option @selected(($filters['quality'] ?? '') === $q)>{{ $q }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-5 col-lg-2">
            <label class="visually-hidden" for="ka-sort">Порядок</label>
            <select id="ka-sort" name="sort" class="form-select">
                <option value="new" @selected(($filters['sort'] ?? 'new') === 'new')>Новые</option>
                <option value="old" @selected(($filters['sort'] ?? '') === 'old')>Старые</option>
                <option value="az" @selected(($filters['sort'] ?? '') === 'az')>А–Я</option>
            </select>
        </div>
    </div>
    <div class="ka-stages mt-2" role="group" aria-label="Ступень">
        <a class="btn btn-sm rounded-pill {{ $category ? 'btn-outline-primary' : 'btn-primary' }}" href="{{ route('films.index') }}" data-ka-cat="">Все</a>
        @foreach($categories as $c)
            <a class="btn btn-sm rounded-pill {{ $category && $category->id === $c->id ? 'btn-primary' : 'btn-outline-primary' }}"
               href="{{ route('films.category', $c) }}" data-ka-cat="{{ $c->id }}">{{ $c->name }} <span class="opacity-75">· {{ $c->grades() }}</span></a>
        @endforeach
    </div>
    <noscript><button class="btn btn-primary btn-sm mt-2">Показать</button></noscript>
    <div class="small text-muted mt-2" data-ka-found aria-live="polite"></div>
</form>

{{-- Сетка: на проде .row.films + .film-card{width:24%} — из-за этого на телефоне 3–4 крошечные карточки.
     KINOUROKI-ADAPTIVE: сетка переопределена в kinouroki-adaptive.css (1 → 2 → 3 → 4 колонки). --}}
<div class="row films row-cols-1 g-0 align-items-start" data-ka-grid>
    @forelse($films as $film)
        <x-film-card :film="$film" />
    @empty
        <p class="text-muted">По этим условиям фильмов нет.</p>
    @endforelse
</div>
<div class="ka-empty text-center text-muted py-5" hidden data-ka-empty>
    По этим условиям фильмов нет. <button type="button" class="btn btn-link p-0 align-baseline" data-ka-reset>Сбросить фильтры</button>
</div>
@endsection
