<?php

namespace App\Http\Controllers;

use App\Models\News;

class NewsController extends Controller
{
    public function index()
    {
        return view('news.index', ['news' => News::query()->orderBy('id')->get()]);
    }

    public function show(News $news)
    {
        return view('news.show', ['n' => $news, 'latest' => News::whereKeyNot($news->id)->limit(3)->get()]);
    }
}
