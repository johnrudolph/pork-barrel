<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// @todo do we need this? It's weird to have a class and a model. at least rename?
class Headline extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function round()
    {
        return $this->belongsTo(Round::class);
    }
}
