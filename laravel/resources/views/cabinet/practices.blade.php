{{-- /practies, /initiatives, /practies/user/{id}, /practices/draft — разметка прода, данные — примеры. --}}
@extends('layouts.app')
@section('title', $title)
@section('body_class', 'practies-section')
@section('content')
<div class="row">
    <div class="{{ $withFilter ? 'col-md-7 col-lg-8 order-2 order-md-0' : 'col-lg-12' }} practies">
        <div class="font-size-28 fw-light text-muted"> {{ $title }} @if(!is_null($total))<span class="badge badge-primary ms-3"> {{ $total }} </span>@endif</div>

        @if(empty($practices))
            {{-- Прод: пустой список не показывает НИЧЕГО. KINOUROKI-ADAPTIVE: понятное пустое состояние --}}
            <div class="card p-4 mt-3 text-center fw-light ka-empty-state">
                <div class="font-size-20 mb-2">Здесь пока пусто</div>
                <p class="text-muted mb-3">Проведите киноурок и опубликуйте социальную практику класса — она появится в этом списке.</p>
                <a class="btn btn-primary mx-auto" href="{{ route('cabinet.lessons') }}">Выбрать киноурок</a>
            </div>
        @else
        {{-- Прод: row-cols-lg-{{ $cols }} — на планшете 1 колонка. KINOUROKI-ADAPTIVE: + row-cols-sm-2 --}}
        <div class="row align-items-start row-cols-1 row-cols-sm-2 row-cols-lg-{{ $cols }} mt-3 g-3">
            @foreach($practices as $p)
            <div class="col">
                <a class="card text-decoration-none text-dark h-100" href="{{ config('kinouroki.prod_url') }}/practies">
                    <img alt="Фото социальной практики (пример)" class="card-img-top ka-cover" src="{{ asset('img/placeholder.svg') }}" loading="lazy">
                    <div class="card-body practice-card ">
                        <div class="row g-0 align-items-center justify-content-between">
                            <div class="small text-muted mb-2 d-flex w-100">
                                <div class="me-2"> №{{ $p['id'] }} · {{ $p['date'] }} </div>
                                {{-- Прод: здесь внутри КАЖДОЙ карточки стоит отдельный <style>. Перенесено в CSS. --}}
                                <div class="ms-auto d-flex">
                                    @if($p['moderated'])
                                    <div class="text-end d-flex align-center" title="Проверено модератором">
                                        <img class="moderator_avatar rounded-circle ms-1" src="{{ asset('img/placeholder.svg') }}" width="20" height="20" alt="Модератор">
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        {{-- KINOUROKI-ADAPTIVE: название практики в 2 строки вместо text-truncate --}}
                        <h4 class="ka-clamp-2 col px-0 font-size-20">{{ $p['title'] }}</h4>
                        <div class="font-size-14 fw-light my-2">
                            <div> Страна: Россия, {{ $p['city'] }} </div>
                            <div class="ka-clamp-2"> {{ $p['school'] }} </div>
                            <div> Класс: {{ $p['class'] }} </div>
                        </div>
                        @if($p['film'])
                        <div class="font-size-14 fw-light mb-2"> Фильм: {{ $p['film']->title }} </div>
                        <div><span class="font-size-14 fw-light">Качество: </span><span class="me-2 disabled font-size-14 fw-light"> {{ $p['film']->quality }} </span></div>
                        @endif
                        <div class="card-text small text-truncate col px-0"> Автор: <span class="text-muted"> {{ $p['author'] }} </span></div>
                        <div class="card-text small text-truncate col px-0"> Самоанализ: <span class="text-muted {{ $p['self'] > $p['expert'] ? 'text-red' : '' }}"> {{ $p['self'] }} </span></div>
                        <div class="card-text small text-truncate col px-0"> Экспертный анализ: <span class="text-muted"> {{ $p['expert'] }} </span></div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    @if($withFilter)
    <div class="col-md-5 col-lg-4 order-1 order-md-0">
        {{-- KINOUROKI-ADAPTIVE: на телефоне фильтр свёрнут (на проде он занимает весь первый экран) --}}
        <details class="ka-filter-details mt-3 mt-md-4" open>
            <summary class="btn btn-outline-primary w-100 d-md-none">Фильтр и сортировка</summary>
            <form class="form-group-lg mt-2 mt-md-0 card py-3" action="{{ config('kinouroki.prod_url') }}/practies_search">
                <div class="px-3">
                    <div class="fw-light text-muted"> Фильтр </div>
                    <div class="mt-2">
                        <select class="form-select" name="order_by" aria-label="Порядок">
                            <option selected value="0">Сначала новые</option><option value="1">Сначала старые</option>
                            <option value="2">По оценке</option><option value="3">По названию</option>
                        </select>
                    </div>
                    <div><input type="text" class="form-control mt-2 datepicker" name="period" id="period" placeholder="Период"></div>
                    <div><input min="1" type="number" class="form-control mt-2" name="id" id="id" placeholder="Номер практики"></div>
                    <div><input type="text" class="form-control mt-2" name="name" id="name" placeholder="Название практики"></div>
                    <div class="my-2">
                        {{-- Прод: в пунктах «Название | Качество» — на телефоне список обрезан. KINOUROKI-ADAPTIVE: ka-select-wrap --}}
                        <select class="form-select ka-select-wrap" name="lesson_id" aria-label="Киноурок">
                            <option value="0" selected> Киноурок (не выбран)</option>
                            @foreach($films as $f)<option value="{{ $f->lesson_id }}">{{ $f->title }} | {{ $f->quality }}</option>@endforeach
                        </select>
                    </div>
                    <label class="text-muted fw-light" for="country_id">Страна</label>
                    <select name="country_id" id="country_id" class="form-control form-select js-choice">
                        <option value="0">Не выбрано</option><option value="670">Россия</option><option value="675">Беларусь</option><option value="676">Казахстан</option>
                    </select>
                    <div class="row px-4">
                        {{-- Прод: у чекбоксов нет id, поэтому клик по подписи не ставит галочку. KINOUROKI-ADAPTIVE: добавлены id --}}
                        @foreach(['photos' => 'Есть фотографии', 'video' => 'Есть видео', 'docs' => 'Есть вложения', 'initiative' => 'Инициатива'] as $name => $label)
                        <div class="form-check mt-2">
                            <input type="hidden" name="{{ $name }}" value="0">
                            <input type="checkbox" class="form-check-input" name="{{ $name }}" id="f-{{ $name }}">
                            <label class="form-check-label" for="f-{{ $name }}">{{ $label }}</label>
                        </div>
                        @endforeach
                    </div>
                    <div class="col"><button type="button" class="mt-3 btn btn-primary w-100">Применить</button></div>
                </div>
            </form>
        </details>
    </div>
    @endif
</div>

@if(!empty($practices) && $withFilter)
{{-- Пагинация прода: 10+ кнопок в ряд — на телефоне страница уезжает вправо (ширина 406 px при экране 375).
     KINOUROKI-ADAPTIVE: ka-pagination прячет лишние номера на узком экране --}}
<nav class="mt-3 d-flex justify-content-center" aria-label="Страницы">
    <ul class="pagination ka-pagination flex-wrap">
        <li class="page-item disabled"><span class="page-link">‹</span></li>
        <li class="page-item active" aria-current="page"><span class="page-link">1</span></li>
        @foreach(range(2, 8) as $pg)<li class="page-item ka-page-far"><a class="page-link" href="#">{{ $pg }}</a></li>@endforeach
        <li class="page-item disabled"><span class="page-link">...</span></li>
        <li class="page-item"><a class="page-link" href="#">4671</a></li>
        <li class="page-item"><a class="page-link" href="#">4672</a></li>
        <li class="page-item"><a class="page-link" href="#" rel="next">›</a></li>
    </ul>
</nav>
@endif
@endsection
