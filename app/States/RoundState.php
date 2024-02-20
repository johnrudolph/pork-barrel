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

    public Collection $offer_ids;

    public Collection $offers_from_previous_rounds_that_resolve_this_round;

    public string $round_template;

    public function roundModel(): Round
    {
        return Round::find($this->id);
    }

    public function game(): GameState
    {
        return GameState::load($this->game_id);
    }

    public function next(): RoundState
    {
        return $this->game()->rounds()->first(fn ($r) => $r->round_number === $this->round_number + 1);
    }

    public function offers()
    {
        return $this->offer_ids->map(fn ($id) => OfferState::load($id));
    }

    public function actionsWonBy(int $player_id)
    {
        return $this->offers()->filter(fn ($o) => $o->awarded && $o->player_id === $player_id
        );
    }
}
