<?php

namespace App\Models;

use App\Bureaucrats\Bureaucrat;
use App\Events\GameEnded;
use App\Events\GameStarted;
use App\Events\RoundStarted;
use App\RoundModifiers\RoundModifier;
use App\States\GameState;
use Glhd\Bits\Database\HasSnowflakes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function state()
    {
        return GameState::load($this->id);
    }

    public function start()
    {
        GameStarted::fire(game_id: $this->id);

        RoundStarted::fire(
            game_id: $this->id,
            round_id: $this->state()->rounds[0],
            bureaucrats: Bureaucrat::all()->random(5)->toArray(),
            round_modifier: RoundModifier::all()->random(),
        );
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
