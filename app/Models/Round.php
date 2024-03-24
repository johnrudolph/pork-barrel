<?php

namespace App\Models;

use App\Events\RoundStarted;
use App\RoundConstructor\RoundConstructor;
use App\States\RoundState;
use Glhd\Bits\Database\HasSnowflakes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Thunk\Verbs\Facades\Verbs;

class Round extends Model
{
    use HasFactory, HasSnowflakes;

    protected $guarded = [];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function headlines()
    {
        return $this->hasMany(Headline::class);
    }

    public function state()
    {
        return RoundState::load($this->id);
    }

    public function next()
    {
        return $this->game->rounds()
            ->where('round_number', $this->round_number + 1)
            ->first();
    }

    public function previous()
    {
        return $this->game->rounds()
            ->where('round_number', $this->round_number - 1)
            ->first();
    }

    public function start()
    {
        // $constructor = new RoundConstructor(round: $this->state());

        // RoundStarted::fire(
        //     round_id: $this->id,
        //     game_id: $this->game->id,
        //     round_number: $this->round_number,
        //     bureaucrats: $constructor->bureaucrats,
        //     round_template: $constructor->round_template,
        // );

        $template = $this->game->template::rounds();

        RoundStarted::fire(
            round_id: $this->id,
            game_id: $this->game->id,
            round_number: $this->round_number,
            bureaucrats: $template[$this->round_number]['bureaucrats'],
            round_template: $template[$this->round_number]['round_template'],
        );

        Verbs::commit();
    }
}
