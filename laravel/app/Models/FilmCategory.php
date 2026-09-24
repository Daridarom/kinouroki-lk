<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FilmCategory extends Model
{
    protected $guarded = [];

    public function films(): BelongsToMany
    {
        return $this->belongsToMany(Film::class, 'category_film');
    }

    /** Подсказка по классам для чипа на карточке. */
    public function grades(): string
    {
        return [1 => '1–4 класс', 2 => '5–9 класс', 3 => '10–11 класс'][$this->id] ?? '';
    }
}
