<?php

namespace App\Events;

use App\Models\Round;
use Thunk\Verbs\Event;
use App\States\GameState;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;

class RoundStarted extends Event
{
    #[StateId(GameState::class)]
    public int $game_id;

    #[StateId(RoundState::class)]
    public int $round_id;

    public int $round_number;

    public function applyToRoundState(RoundState $state)
    {
        $state->status = 'in-progress';
    }

    public function applyToGameState(GameState $state)
    {
        $state->current_round_id = $this->round_id;
        $state->current_round_number = $this->round_number;
    }
}
