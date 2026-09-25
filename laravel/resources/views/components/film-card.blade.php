{{--
    KINOUROKI-ADAPTIVE: карточка фильма (новый компонент).
    На проде карточка — <a class="film-card …"> с постером и названием в text-truncate.
    Здесь: постер 16:9 без искажений, название в 2 строки, качество, ступени, год.
    Классы прода (film-card, card, rounded…) сохранены, новые — с префиксом ka-.
--}}
@props(['film', 'eager' => false])
<a href="{{ route('films.show', $film) }}"
   class="film-card ka-film-card bg-white card rounded overflow-hidden text-decoration-none"
   data-ka-search="{{ mb_strtolower($film->title.' '.$film->quality.' '.$film->lead(300)) }}"
   data-ka-quality="{{ $film->quality }}"
   data-ka-cats="{{ $film->categories->pluck('id')->join(' ') }}">
    {{-- [KA-053] превью 480×270 WebP вместо оригинала 0,3–1,9 МБ; пока грузится — название фильма на фирменном фоне, а не серый прямоугольник.
         Первые карточки грузятся сразу (eager), остальные — по мере прокрутки. --}}
    <div class="ka-poster" data-ka-poster-title="{{ $film->title }}">
        <img src="{{ $film->posterThumbUrl() }}" alt="{{ $film->title }}" loading="{{ $eager ? 'eager' : 'lazy' }}" @if($eager) fetchpriority="high" @endif decoding="async" width="480" height="270">
        <span class="ka-quality">{{ $film->quality }}</span>
    </div>
    <div class="ka-body">
        <div class="ka-title">{{ $film->title }}</div>
        <div class="ka-meta">
            @foreach($film->categories as $c)
                <span class="badge rounded-pill text-bg-light border fw-normal" title="{{ $c->grades() }}">{{ $c->name }}</span>
            @endforeach
            @if($film->published_at)<span class="text-muted small">{{ $film->published_at->year }}</span>@endif
        </div>
    </div>
</a>
