<?php

namespace App\Events;

use Thunk\Verbs\Event;
use App\States\GameState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;

class GameStarted extends Event
{
    #[StateId(GameState::class)]
    public int $game_id;

    public function applyToGame(GameState $state)
    {
        $state->status = 'in-progress';
    }
}
