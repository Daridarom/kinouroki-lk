@extends('layouts.app')
@section('title', $page['title'])
@section('body_class', $key.'-section')
@php($prod = config('kinouroki.prod_url'))
@php($enc = fn ($p) => implode('/', array_map('rawurlencode', explode('/', $p))))
@section('content')
<div class="font-size-28 title fw-light text-muted mb-3">{{ $page['title'] }}</div>
@php($searchable = collect($page['sections'])->contains(fn ($s) => in_array($s['type'], ['links', 'faq', 'list'])))
@if($searchable)
    {{-- KINOUROKI-ADAPTIVE [KA-024] поиск по списку документов / вопросов / вебинаров --}}
    <div class="ka-live-search mb-3">
        <label class="visually-hidden" for="ka-page-search">Поиск на странице</label>
        <input id="ka-page-search" type="search" class="form-control" placeholder="Найти на странице: слово из названия или вопроса" data-ka-live-search="#ka-page-content" autocomplete="off">
        <div class="small text-muted mt-1" data-ka-live-found aria-live="polite"></div>
    </div>
@endif
<div id="ka-page-content">
@foreach($page['sections'] as $s)
    @switch($s['type'])
        @case('text')
            <div class="card p-3 fw-light mb-3 ka-film-body">
                @isset($s['title'])<h4 class="fw-light text-muted">{{ $s['title'] }}</h4>@endisset
                @foreach(preg_split('/\n\n/', $s['text']) as $para)<p>{!! nl2br(e($para)) !!}</p>@endforeach
            </div>
            @break
        @case('links')
            <h4 class="font-size-26 fw-light text-muted mt-4 mb-0">{{ $s['title'] }} <span class="badge badge-primary ms-3">{{ count($s['items']) }}</span></h4>
            <div class="list-group mt-2 mb-3">
                @foreach($s['items'] as $it)
                    @php($href = str_starts_with($it[1], '~') ? $prod.$enc(($page['base'] ?? '').substr($it[1], 1)) : $prod.$enc($it[1]))
                    <a href="{{ $href }}" target="_blank" rel="noopener" class="list-group-item list-group-item-action"><span class="fw-light">{{ $it[0] }}</span>
                        @isset($it[2])<br><small class="text-danger">{{ $it[2] }}</small>@endisset</a>
                @endforeach
            </div>
            @break
        @case('faq')
            <div class="accordion accordion-flush rounded overflow-hidden shadow-sm" id="accordionFAQ">
                @foreach($s['items'] as $i => [$q, $a])
                {{-- KINOUROKI-ADAPTIVE [KA-025] у каждого вопроса свой адрес #faq-N: ссылкой можно поделиться, вопрос откроется сам --}}
                <div class="accordion-item" id="faq-{{ $i + 1 }}">
                    <h2 class="accordion-header" id="h{{ $i }}"><button class="accordion-button collapsed fw-light" type="button" data-bs-toggle="collapse" data-bs-target="#c{{ $i }}" aria-expanded="false" aria-controls="c{{ $i }}">{{ $q }}</button></h2>
                    <div id="c{{ $i }}" class="accordion-collapse collapse" aria-labelledby="h{{ $i }}" data-bs-parent="#accordionFAQ">
                        <div class="accordion-body fw-light">@foreach(preg_split('/\n\n/', $a) as $para)<p>{{ $para }}</p>@endforeach
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-ka-copy-link="#faq-{{ $i + 1 }}">Скопировать ссылку на вопрос</button></div>
                    </div>
                </div>
                @endforeach
            </div>
            @break
        @case('list')
            <div class="list-group">
                @foreach($s['items'] as [$t, $d, $href])
                    <a class="list-group-item list-group-item-action d-flex flex-column flex-sm-row justify-content-between gap-1" href="{{ $prod.$href }}"><span class="fw-light">{{ $t }}</span><small class="text-muted text-nowrap">{{ $d }}</small></a>
                @endforeach
            </div>
            @break
        @case('cards')
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
                @foreach($s['items'] as [$t, $d, $file, $img])
                <div class="col"><a class="text-decoration-none d-flex flex-column align-items-center text-dark" href="{{ $prod.$enc($s['file_base'].substr($file, 1)) }}" target="_blank" rel="noopener">
                    <img class="img-fluid shadow-sm mb-2" src="{{ $prod.$enc($s['img_base'].substr($img, 1)) }}" alt="Журнал «Искусство созидать» {{ $t }}" loading="lazy">
                    <div class="font-size-12 text-muted">{{ $d }}</div><div class="fw-light text-center">{{ $t }}</div></a></div>
                @endforeach
            </div>
            @break
        @case('note')
            <p class="small text-muted mt-3">{{ $s['text'] }}</p>
            @break
    @endswitch
@endforeach
</div>
@endsection
