<?php

namespace App\Events;

use App\States\GameState;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class RoundStarted extends Event
{
    public function __construct(
        #[StateId(GameState::class)] public int $game_id,
        #[StateId(RoundState::class)] public int $round_id,
        public int $round_number,
        public $bureaucrats
    ) {
    }

    public function applyToRoundState(RoundState $state)
    {
        $state->status = 'in-progress';
        $state->phase = 'auction';
        $state->bureaucrats = $this->bureaucrats;
    }

    public function applyToGameState(GameState $state)
    {
        $state->current_round_id = $this->round_id;
        $state->current_round_number = $this->round_number;
    }
}
