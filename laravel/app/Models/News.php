<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $guarded = [];

    protected $casts = ['categories' => 'array', 'links' => 'array'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function imageUrl(): ?string
    {
        return Film::prod($this->image ? '/storage/news/'.$this->image : null);
    }
}
