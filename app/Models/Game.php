<?php

namespace App\Models;

use App\Events\GameEnded;
use App\Events\GameStarted;
use App\States\GameState;
use Glhd\Bits\Database\HasSnowflakes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Thunk\Verbs\Facades\Verbs;

class Game extends Model
{
    use HasFactory, HasSnowflakes;

    protected $guarded = [];

    protected $casts = [
        'is_transparent' => 'boolean',
    ];

    public function players()
    {
        return $this->hasMany(Player::class);
    }

    public function rounds()
    {
        return $this->hasMany(Round::class);
    }

    public function headlines()
    {
        return $this->hasMany(Headline::class);
    }

    public function state()
    {
        return GameState::load($this->id);
    }

    public function start()
    {
        GameStarted::fire(game_id: $this->id);

        Verbs::commit();

        $this->rounds->first()->start();
    }

    public function currentRound()
    {
        return Round::find($this->state()->current_round_id);
    }

    public function end()
    {
        GameEnded::fire(game_id: $this->id);
    }
}
