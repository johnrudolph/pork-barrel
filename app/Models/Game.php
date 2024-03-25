<?php

namespace App\Models;

use App\Events\GameEnded;
use App\States\GameState;
use App\Events\GameStarted;
use Thunk\Verbs\Facades\Verbs;
use Glhd\Bits\Database\HasSnowflakes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Game extends Model
{
    use HasFactory, HasSnowflakes;

    protected $guarded = [];

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
