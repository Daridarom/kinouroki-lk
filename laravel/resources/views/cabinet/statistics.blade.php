{{-- /statistics/* — оболочка страницы статистики, разметка прода.
     Таблицы на проде рисует Vue-компонент <statistics-wrapper> из app.js по API —
     в копии API нет, поэтому показана таблица-пример (как в отчёте по округам). --}}
@extends('layouts.app')
@section('title', 'Статистика')
@section('body_class', 'statistics-section')
@php($tabs = ['preschools' => 'ДОУ', 'classes' => 'Класс', 'schools' => 'Школа', 'municipalities' => 'МО', 'activities' => 'Активности', 'premia' => 'Народная премия'])
@section('content')
<div class="row"><div class="col-12 font-size-36 text-center text-sm-start"> Статистика</div></div>

<div class="row mt-3 mt-sm-5 font-size-24 justify-content-center justify-content-md-start mx-2 mx-sm-0 gap-2">
    <div class="col-auto switch-button-left py-1" style="background-color: #4582EC; color: white"><a class="text-white" href="{{ route('cabinet.statistics', 'classes') }}"> Статистика</a></div>
    <div class="col-auto switch-button-right py-1"><a href="{{ route('cabinet.statistics', 'compare') }}">Общая статистика</a></div>
    <div class="col-auto py-1">
        {{-- Прод: data-toggle / data-target — это атрибуты Bootstrap 4. На сайте Bootstrap 5, поэтому подсказки НЕ ОТКРЫВАЮТСЯ.
             KINOUROKI-ADAPTIVE: data-bs-toggle / data-bs-target + btn-close --}}
        <button type="button" class="btn btn-link p-0" data-bs-toggle="modal" data-bs-target="#statisticsModal" aria-label="Подсказка">
            <img style="max-height: 25px; width: auto" src="{{ config('kinouroki.prod_url') }}/images/pages/statistics/question.png" alt="">
        </button>
    </div>
</div>

<div class="modal fade" id="statisticsModal" tabindex="-1" aria-labelledby="statisticsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" id="statisticsModalLabel"> Статистика </h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button></div>
        <div class="modal-body">
            <p><b>Активности</b> – Количество выполненных социальных практик, проектов и профориентационных проектов, в том числе, при поддержке органов власти</p>
            <p><b>Классы</b> – Количество классов, задействованных в системе воспитания «Киноуроки в школах России»; статистика выполненных классами социальных практик, проектов и профориентационных проектов.</p>
            <p><b>Школы</b> – Количество школ каждого типа (общеобразовательных, специализированных и др.), задействованных в системе воспитания «Киноуроки в школах России»; статистика выполненных социальных практик, проектов и профориентационных проектов школами каждого типа.</p>
        </div>
    </div></div>
</div>

<div class="col-lg-4 col-12 my-4 font-size-24 text-center text-lg-start">
    <a class="green_underline" href="{{ route('cabinet.statistics', 'report') }}"> Статистический отчет </a>
</div>

{{-- KINOUROKI-ADAPTIVE: разделы — прокручиваемая строка вкладок вместо «рассыпанных» ссылок крупным шрифтом --}}
<nav class="nav nav-pills ka-tabs mb-3" aria-label="Разделы статистики">
    @foreach($tabs as $key => $label)
        <a class="nav-link {{ $section === $key ? 'active' : '' }}" href="{{ route('cabinet.statistics', $key) }}">{{ $label }}</a>
    @endforeach
</nav>

<div class="card p-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div class="font-size-24 fw-light">Отчет по федеральным округам</div>
        <button class="btn btn-sm btn-primary" type="button" disabled>Экспорт в Excel</button>
    </div>
    {{-- Прод: таблица 711 px внутри экрана 375 px без прокрутки — правая часть обрезана.
         KINOUROKI-ADAPTIVE: .table-responsive --}}
    <div class="table-responsive">
        <table class="table table-sm w-100 font-size-17 ka-table">
            <thead><tr><th>Округ</th><th>Школ</th><th>Педагогов</th><th>Классов</th><th>Практик</th><th>Средняя оценка</th><th>МО с поддержкой</th></tr></thead>
            <tbody>
            @foreach(['Центральный', 'Северо-Западный', 'Южный', 'Северо-Кавказский', 'Приволжский', 'Уральский', 'Сибирский', 'Дальневосточный'] as $i => $okrug)
                <tr><td>{{ $okrug }} (пример)</td><td>{{ 120 + $i * 37 }}</td><td>{{ 540 + $i * 91 }}</td><td>{{ 980 + $i * 113 }}</td><td>{{ 4200 + $i * 517 }}</td><td>{{ number_format(10.2 + $i * .3, 1, ',', '') }}</td><td>{{ 12 + $i }}</td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <p class="small text-muted mb-0">Цифры — пример. На проде таблицу заполняет Vue-компонент по API.</p>
</div>
@endsection
