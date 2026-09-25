{{-- /users/{id} — главная страница кабинета. Разметка прода, данные — пример. --}}
@extends('layouts.app')
@section('title', 'Личный кабинет')
@section('body_class', 'users.show-section')
@section('content')
{{-- KINOUROKI-ADAPTIVE [KA-043] на телефоне сначала карточка педагога и «Активность», потом новости (прод: 3 блока новостей сверху, профиль — ниже двух экранов).
     Порядок меняется только классами order-*; на компьютере вид прода. --}}
<div class="d-flex flex-column">
<div class="col-lg-12 order-2 order-lg-1 mt-3 mt-lg-0">
    @foreach(['Важное', 'Педагогам', 'Акции'] as $cat)
    <div class="card p-3 ms-lg-auto {{ $loop->first ? '' : 'mt-3' }}">
        {{-- Прод: class="… justify-content-between flex" — класса «flex» в Bootstrap нет, шапка карточки не выстраивается в строку.
             KINOUROKI-ADAPTIVE: d-flex flex-wrap gap-2 --}}
        <div class="fw-light font-size-20 text-muted mb-3 justify-content-between d-flex flex-wrap gap-2 align-items-center">
            <div>Новости / {{ $cat }}</div>
            <a class="btn btn-primary btn-sm ka-btn-auto" href="{{ route('news.index') }}">Смотреть все новости...</a>
        </div>
        <ul class="list-group list-group-flush">
            @foreach($news as $n)
            <a class="list-group-item list-group-item-action" href="{{ route('news.show', $n) }}">
                <div class="row g-0 align-items-center justify-content-between">
                    {{-- KINOUROKI-ADAPTIVE: на телефоне заголовок в 2 строки вместо обрезки --}}
                    <div class="col-12 col-sm-8 px-0 ka-clamp-2 fw-light"> {{ $n->title }} </div>
                    <div class="col-auto px-0 fw-light small text-muted"> {{ $n->published_label }} </div>
                </div>
            </a>
            @endforeach
        </ul>
    </div>
    @endforeach
</div>
<div class="row justify-content-between align-items-start mt-lg-3 order-1 order-lg-2">
    <div class="col-lg-6">
        <div class="row g-0 row-cols-auto bg-white shadow-sm rounded p-3">
            <div class="mx-auto">
                <div title="В сети" class="position-relative d-inline-block">
                    <img alt="Фото профиля" class="p-0 me-2 shadow-sm border border-light rounded-circle img-fluid" src="{{ asset('img/placeholder.svg') }}" style="height: 140px; width: 140px; object-fit: cover;">
                    {{-- Прод: position:absolute с «магическим» margin 120px 0 0 -50px. KINOUROKI-ADAPTIVE: привязка к фото --}}
                    <svg fill="none" viewBox="0 0 28 28" width="20" class="ka-online-dot" aria-hidden="true"><circle cx="14" cy="10" fill="#14AD75" r="10"></circle></svg>
                </div>
            </div>
            <div class="col-12 col-lg-8 mt-3 mt-lg-0">
                <div class="font-size-18 pt-0 mt-0"> Иванова Мария Петровна (пример) </div>
                <div class="role mt-2 font-size-12 text-muted fw-light">Педагог</div>
                <div class="mt-4">
                    <div class="fw-light"> Телефон: <span class="text-muted fw-light">Скрыт</span></div>
                    <div class="fw-light mt-1"> E-Mail: <span class="text-muted fw-light">Скрыт</span></div>
                </div>
                <div class="fw-light mt-1 text-muted ka-break"> Россия, 000000, Примерская обл, г Пример, ул Школьная, д 1, МУНИЦИПАЛЬНОЕ БЮДЖЕТНОЕ ОБЩЕОБРАЗОВАТЕЛЬНОЕ УЧРЕЖДЕНИЕ «СРЕДНЯЯ ОБЩЕОБРАЗОВАТЕЛЬНАЯ ШКОЛА № 1 С УГЛУБЛЁННЫМ ИЗУЧЕНИЕМ ОТДЕЛЬНЫХ ПРЕДМЕТОВ» </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card p-3 ms-lg-auto mt-3 mt-lg-0">
            <div class="fw-light font-size-20 text-muted mb-3"> Активность </div>
            <a class="text-dark text-decoration-none pb-2" href="{{ route('cabinet.practices.user') }}"><div class="text-muted fw-light"> Последние социальные практики </div></a>
            <ul class="list-group list-group-flush">
                <a class="btn btn-primary btn-sm mt-3" href="{{ route('cabinet.practices.user') }}">Список всех социальных практик...</a>
            </ul>
        </div>
    </div>
</div>
</div>
@endsection
