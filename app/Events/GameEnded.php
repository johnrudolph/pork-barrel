<?php

namespace App\Events;

use App\States\GameState;
use App\States\PlayerState;
use Glhd\Bits\Snowflake;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class GameEnded extends Event
{
    #[StateId(GameState::class)]
    public int $game_id;

    public function applyToGame(GameState $state)
    {
        $state->status = 'ended';
    }
}
