@extends('layouts.app')
@section('title', 'Новости')
@section('body_class', 'news-section')
@section('content')
<div class="font-size-28 title fw-light text-muted mb-3">Новости <span class="badge badge-primary ms-3">{{ $news->count() }}</span></div>
{{-- Разметка карточек как на проде (card + card-img-top), сетка row-cols адаптивная --}}
<div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 g-3">
    @foreach($news as $n)
    <div class="col">
        <a class="card text-decoration-none text-dark h-100" href="{{ route('news.show', $n) }}">
            <img class="card-img-top ka-cover" src="{{ $n->imageUrl() }}" alt="" loading="lazy">
            <div class="card-body">
                <h5 class="card-title font-size-20 fw-light text-dark col px-0">{{ $n->title }}</h5>
                <p class="card-text fw-light"><small class="text-muted">{{ $n->published_label }}</small></p>
            </div>
        </a>
    </div>
    @endforeach
</div>
@endsection
