<?php

namespace App\Events;

use App\States\GameState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class GameStarted extends Event
{
    #[StateId(GameState::class)]
    public int $game_id;

    public function validateGame(GameState $game)
    {
        $this->assert($game->status !== 'in-progress', 'The game is already active.');
    }

    public function applyToGame(GameState $state)
    {
        $state->status = 'in-progress';
    }
}
