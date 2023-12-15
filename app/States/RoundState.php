<?php

namespace App\States;

use Thunk\Verbs\State;
use Illuminate\Support\Collection;

class RoundState extends State
{
    public int $game_id;

    public string $status = 'upcoming';

    public string $phase = '';

    public int $round_number;

    public Collection $bureaucrats;

    public Collection $offers;

    public Collection $actions_awarded;

    public Collection $blocked_actions;

    public Collection $actions_from_previous_rounds_that_resolve_this_round;

    public string $round_modifier;

    const HOOKS = [
        'on_round_started' => 'on_round_started',
        'on_offer_submitted' => 'on_offer_submitted',
        'on_awarded' => 'on_awarded',
        'on_round_ended' => 'on_round_ended',
    ];

    public function game(): GameState
    {
        return GameState::load($this->game_id);
    }
}
