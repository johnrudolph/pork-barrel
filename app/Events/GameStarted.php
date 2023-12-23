<?php

namespace App\Events;

use Thunk\Verbs\Event;
use App\States\GameState;
use App\Events\GameUpdated;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;

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

    public function handle()
    {
        GameUpdated::dispatch($this->game_id);
    }
}
