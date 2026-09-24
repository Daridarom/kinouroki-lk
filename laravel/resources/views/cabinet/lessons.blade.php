{{-- /lessons — список киноуроков, разметка прода --}}
@extends('layouts.app')
@section('title', 'Киноуроки')
@section('body_class', 'lessons.index-section')
@section('content')
<div class="font-size-28 fw-light text-muted title"> Киноуроки <span class="badge badge-primary ms-3">{{ $films->count() }}</span></div>
{{-- KINOUROKI-ADAPTIVE: на проде .cards-lessons + col-lg-6 — кнопка качества в карточке шире экрана (417 px на 375).
     Сетка row-cols и перенос текста в метке качества. --}}
<div class="cards-lessons mt-3 row row-cols-1 row-cols-lg-2 g-3">
    @foreach($films as $f)
    <div class="col">
        <a class="card text-decoration-none text-dark h-100" href="{{ route('cabinet.lesson', $f->lesson_id) }}">
            <div class="row g-0 align-items-center justify-content-center">
                <div class="card-body col col-lg-8">
                    <div class="card-title font-size-20 fw-light">{{ $f->title }}</div>
                    <div class="row g-0 justify-content-between align-items-center">
                        <div class="small fw-light">Фильм: {{ $f->title }} </div>
                        <div class="text-muted small fw-light"> {{ $f->published_at?->translatedFormat('d F Y') }} </div>
                    </div>
                </div>
                <div class="card-text">
                    <span class="me-2 btn btn-primary btn-sm rounded-pill font-size-14 disabled m-2 ka-pill-wrap">{{ $f->quality }}</span>
                </div>
            </div>
        </a>
    </div>
    @endforeach
</div>
@endsection
