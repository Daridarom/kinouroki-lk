@extends('layouts.app')
@section('title', 'Фильмы | '.$film->title)
@section('body_class', 'films.show-section')

@section('content')
<div class="col-lg-11 ka-film">
    {{-- Шапка — как на проде; добавлены чипы ступеней (KINOUROKI-ADAPTIVE) --}}
    <h1 class="fw-light font-size-28 text-muted ka-h1">{{ $film->title }}</h1>{{-- [KA-048] --}}
    <div class="row row-cols-lg-auto mt-3 g-0 align-items-center justify-content-between">
        <div class="fw-light mb-3 mb-lg-0 d-flex flex-wrap align-items-center gap-2">
            Качество: <span class="btn btn-primary btn-sm rounded-pill font-size-14 disabled"> {{ $film->quality }} </span>
            @foreach($film->categories as $c)
                <a href="{{ route('films.category', $c) }}" class="badge rounded-pill text-bg-light border fw-normal text-decoration-none">{{ $c->name }} · {{ $c->grades() }}</a>
            @endforeach
        </div>
        <div class="text-right text-muted small mt-3 mt-md-0">Опубликован: <span>{{ $film->published_at?->translatedFormat('d F Y') }}</span></div>
    </div>

    {{-- KINOUROKI-ADAPTIVE: на телефоне блоки меняются местами — сначала видео и кнопка
         «Провести киноурок», потом длинное описание. На компьютере порядок как на проде. --}}
    <div class="d-flex flex-column">
        <div class="mt-3 card p-3 pt-4 fw-light ka-film-body order-2 order-lg-1">
            {{-- KINOUROKI-ADAPTIVE [KA-S03] HTML из редактора выводится только после очистки --}}
            {!! \App\Support\SafeHtml::clean($film->description) !!}
            @foreach($film->links ?? [] as [$label, $href])
                <p><a class="btn btn-primary" href="{{ $href }}" target="_blank" rel="noopener">{{ $label }}</a></p>
            @endforeach
        </div>

        <div class="d-flex flex-column order-1 order-lg-2 ka-film-media">
            @if($film->lesson_id)
                {{-- KINOUROKI-ADAPTIVE: главная кнопка видна сразу на телефоне --}}
                <a class="btn btn-primary w-100 mt-3 d-lg-none" href="{{ route('cabinet.lesson', $film->lesson_id) }}">Провести киноурок по фильму</a>
            @endif
            <nav class="mt-3">
                {{-- KINOUROKI-ADAPTIVE: ka-tabs — вкладки прокручиваются вбок, а не переносятся в столбик --}}
                <div class="nav nav-tabs ka-tabs" id="nav-tab" role="tablist">
                    @if($film->triller)
                        <button class="nav-item nav-link text-muted active" id="nav-triller-tab" data-bs-toggle="tab" data-bs-target="#nav-triller" type="button" role="tab" aria-controls="nav-triller" aria-selected="true"> Трейлер </button>
                    @endif
                    @if($film->video)
                        <button class="nav-item nav-link text-muted {{ $film->triller ? '' : 'active' }}" id="nav-film-tab" data-bs-toggle="tab" data-bs-target="#nav-film" type="button" role="tab" aria-controls="nav-film" aria-selected="{{ $film->triller ? 'false' : 'true' }}"> Фильм </button>
                    @endif
                    @if($film->photos)
                        <button class="nav-item nav-link text-muted" id="nav-photos-tab" data-bs-toggle="tab" data-bs-target="#nav-photos" type="button" role="tab" aria-controls="nav-photos" aria-selected="false"> Фотографии </button>
                    @endif
                    @if($film->time)
                        <button class="nav-item nav-link text-muted" id="nav-tims-tab" data-bs-toggle="tab" data-bs-target="#nav-time" type="button" role="tab" aria-controls="nav-time" aria-selected="false"> Сроки </button>
                    @endif
                    @if($film->lesson_id)
                        <a class="nav-item nav-link btn-seccondary bg-outline-primary text-primary d-none d-lg-block" href="{{ route('cabinet.lesson', $film->lesson_id) }}" role="tab" aria-selected="false"> Провести киноурок по фильму </a>
                    @endif
                </div>
            </nav>
            <div class="tab-content border-0 shadow-none" id="nav-tabContent">
                {{-- KINOUROKI-ADAPTIVE: плееры не грузятся заранее — iframe/video создаётся из data-ka-src
                     при открытии вкладки (js/kinouroki-adaptive.js). На проде грузятся сразу все. --}}
                @if($film->triller)
                <div class="tab-pane show active" id="nav-triller" role="tabpanel" aria-labelledby="nav-triller-tab">
                    <div class="border border-top-0 rounded-3 p-3"><div class="card-body">
                        <div class="ratio ratio-16x9 rounded overflow-hidden mb-4 ka-player" data-ka-src="{{ $film->trillerIsFile() ? \App\Models\Film::prod($film->triller) : $film->triller }}" data-ka-poster="{{ $film->posterThumbs()[1] ?? $film->posterUrl() }}"></div>
                    </div></div>
                </div>
                @endif
                @if($film->video)
                <div class="tab-pane fade {{ $film->triller ? '' : 'show active' }}" id="nav-film" role="tabpanel" aria-labelledby="nav-film-tab">
                    <div class="border border-top-0 rounded-3 p-3"><div class="card-body">
                        <div class="ratio ratio-16x9 rounded overflow-hidden mb-4 ka-player" data-ka-src="{{ $film->video }}" data-ka-poster="{{ $film->posterThumbs()[1] ?? $film->posterUrl() }}"></div>
                    </div></div>
                </div>
                @endif
                @if($film->photos)
                <div class="tab-pane fade" id="nav-photos" role="tabpanel" aria-labelledby="nav-photos-tab">
                    <div class="border border-top-0 rounded-3 p-3"><div class="card-body">
                        <div id="lightgallery" class="row g-2">
                            @foreach($film->photos as $i => $p)
                                {{-- KINOUROKI-ADAPTIVE: col-6 на телефоне, осмысленный alt (на проде у всех «Название фильма») --}}
                                <a href="{{ \App\Models\Film::prod($p) }}" class="col-6 col-lg-3">
                                    <img src="{{ \App\Models\Film::prod($p) }}" alt="Кадр со съёмок фильма «{{ $film->title }}», фото {{ $i + 1 }}" class="img-thumbnail img-fluid d-block" loading="lazy">
                                </a>
                            @endforeach
                        </div>
                    </div></div>
                </div>
                @endif
                @if($film->time)
                <div class="tab-pane fade" id="nav-time" role="tabpanel" aria-labelledby="nav-tims-tab">
                    <div class="border border-top-0 rounded-0 p-3"><div class="card-body">
                        <div class="fw-light">{!! nl2br(e($film->time)) !!}</div>
                    </div></div>
                </div>
                @endif
            </div>
        </div>
    </div>

    @if($related->isNotEmpty())
        {{-- KINOUROKI-ADAPTIVE: «Ещё для этой ступени» --}}
        <h2 class="font-size-28 fw-light text-muted mt-4 mb-3 ka-h1">Ещё для этой ступени</h2>
        <div class="row films row-cols-1 g-0 align-items-start">
            @foreach($related as $r)<x-film-card :film="$r" />@endforeach
        </div>
    @endif
</div>
@endsection
