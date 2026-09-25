@extends('layouts.app')
@section('title', $n->title)
@section('body_class', 'news-section')
@section('content')
<div class="col-lg-9">
    <h1 class="font-size-28 fw-light text-muted mb-2 ka-h1">{{ $n->title }}</h1>{{-- [KA-048] --}}
    <div class="small text-muted mb-3">{{ implode(', ', $n->categories ?? []) }} · {{ $n->published_label }}</div>
    <img src="{{ $n->imageUrl() }}" alt="" class="img-fluid rounded mb-3 ka-cover">
    <div class="card p-3 fw-light ka-film-body">
        @foreach(preg_split('/\n\n/', $n->body) as $para)<p>{!! nl2br(e($para)) !!}</p>@endforeach
        @foreach($n->links ?? [] as [$t, $href])<p><a class="btn btn-primary" href="{{ $href }}" target="_blank" rel="noopener">{{ $t }}</a></p>@endforeach
        @if($n->film_slug)<p><a class="btn btn-outline-primary" href="{{ route('films.show', $n->film_slug) }}">Открыть фильм</a></p>@endif
        <p class="small text-muted mb-0">Текст в копии сокращён.</p>
    </div>
</div>
@endsection
