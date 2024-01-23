<?php

namespace App\States;

use App\Models\Round;
use Illuminate\Support\Collection;
use Thunk\Verbs\State;

class RoundState extends State
{
    public int $game_id;

    public string $status = 'upcoming';

    public int $round_number;

    public Collection $bureaucrats;

    public Collection $blocked_bureaucrats;

    public Collection $offers;

    public Collection $offers_from_previous_rounds_that_resolve_this_round;

    public string $round_modifier;

    public function roundModel(): Round
    {
        return Round::find($this->id);
    }

    public function game(): GameState
    {
        return GameState::load($this->game_id);
    }

    public function actionsWonBy(int $player_id)
    {
        return $this->offers->filter(fn ($o) => $o->awarded && $o->player_id === $player_id
        );
    }
}
