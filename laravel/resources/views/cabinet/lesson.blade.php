{{-- /lessons/{id} — страница киноурока, разметка прода --}}
@extends('layouts.app')
@section('title', 'Киноуроки | '.$film->title)
@section('body_class', 'lessons.show-section')
@section('content')
<div class="card">
    <div class="card-body">
        <h1 class="fw-light font-size-28 mb-4 ka-h1">{{ $film->title }}</h1>{{-- [KA-048] --}}
        <div class="row g-0 mt-3 pt-3 border-top">
            <div class="col-lg-8">
                @if($film->video)
                <div class="ratio ratio-16x9 rounded overflow-hidden mb-4 ka-player" data-ka-src="{{ $film->video }}" data-ka-poster="{{ $film->posterThumbs()[1] ?? $film->posterUrl() }}"></div>
                @endif
                <a class="btn btn-primary btn-sm mb-3 rounded d-block" href="{{ route('films.show', $film) }}">Подробнее о фильме</a>
                <div class="my-3 border-top py-3 border-bottom fw-light ka-film-body">
                    {{-- KINOUROKI-ADAPTIVE [KA-S03] HTML из редактора выводится только после очистки --}}
            {!! \App\Support\SafeHtml::clean($film->description) !!}
                </div>
                <p class="card-text mt-auto fw-light"><span class="btn btn-primary btn-sm rounded-pill font-size-14 disabled btn-sm ka-pill-wrap">{{ $film->quality }}</span></p>
            </div>
            <div class="col-lg-4 ps-lg-3">
                <div class="d-flex flex-column mb-4 mt-3 mt-lg-0">
                    <a href="{{ config('kinouroki.prod_url') }}/practies_search?lesson_id={{ $film->lesson_id }}&amp;order_by=4" class="btn btn-outline-primary mt-2"> Практики киноурока <span class="btn btn-sm bg-danger text-white rounded-circle fw-bold font-size-14 ms-2">300</span></a>
                </div>
                <div class="d-flex flex-column mb-5">
                    <div class="mb-3 fw-light font-size-20 border-bottom text-muted pb-2">Методические материалы и файлы</div>
                    <div class="list-group">
                        {{-- Прод: имя файла без пробелов «МР_Требовательность_к_себе_фильм_…pdf» не переносится и выходит за экран.
                             KINOUROKI-ADAPTIVE: .ka-break --}}
                        <a href="{{ $film->links[0][1] ?? '#' }}" target="_blank" rel="noopener" class="list-group-item list-group-item-action list-group-item-primary ka-break">МР_{{ str_replace(' ', '_', $film->quality) }}_фильм_{{ str_replace(' ', '_', $film->title) }}.pdf</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
