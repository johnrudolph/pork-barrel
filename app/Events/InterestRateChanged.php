<?php

namespace App\Events;

use App\States\GameState;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class InterestRateChanged extends Event
{
    #[StateId(GameState::class)]
    public int $game_id;

    #[StateId(RoundState::class)]
    public int $round_id;

    public float $amount;

    public function applyToGame(GameState $state)
    {
        $state->interest_rate += $this->amount;
    }

    public function applyToRound(RoundState $state)
    {
        //
    }
}
