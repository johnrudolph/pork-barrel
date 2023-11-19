<?php

namespace App\States;

use Thunk\Verbs\State;

class RoundState extends State
{
    public int $game_id;

    public string $status = 'upcoming';

    public string $phase = '';

    public int $round_number;

    public $bureaucrats;

    public $offers;

    public $auction_winners;

    public $actions;

    public $blocked_actions;

    public function gameState()
    {
        return GameState::load($this->game_id);
    }
}
