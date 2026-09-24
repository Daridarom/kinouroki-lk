<?php

namespace App\Http\Controllers;

use App\Models\Film;
use App\Models\FilmCategory;
use Illuminate\Http\Request;

class FilmController extends Controller
{
    /** /films и /films/category/{category} — как на проде, плюс фильтры (KINOUROKI-ADAPTIVE). */
    public function index(Request $request, ?FilmCategory $category = null)
    {
        $filters = $request->only(['q', 'quality', 'sort']);
        if ($category) {
            $filters['category'] = $category->id;
        }

        // KINOUROKI-ADAPTIVE: все фильмы одной страницей (67 карточек, картинки lazy),
        // вместо paginate(32). Если фильмов станет > 150 — вернуть paginate(48)->withQueryString().
        $films = Film::query()->with('categories')->filter($filters)->get();

        return view('films.index', [
            'films' => $films,
            'total' => Film::count(),
            'category' => $category,
            'categories' => FilmCategory::all(),
            'qualities' => Film::query()->orderBy('quality')->distinct()->pluck('quality'),
            'filters' => $filters,
        ]);
    }

    public function show(Film $film)
    {
        $film->load('categories');
        $related = Film::query()
            ->whereKeyNot($film->id)
            ->whereHas('categories', fn ($q) => $q->whereIn('film_categories.id', $film->categories->pluck('id')))
            ->latest('published_at')->limit(4)->get();

        return view('films.show', compact('film', 'related'));
    }
}
