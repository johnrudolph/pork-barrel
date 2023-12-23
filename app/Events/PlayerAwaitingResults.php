<?php

namespace App\Events;

use Thunk\Verbs\Event;
use App\States\PlayerState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;

class PlayerAwaitingResults extends Event
{
    #[StateId(PlayerState::class)]
    public int $player_id;

    public function applyToPlayerState(PlayerState $state)
    {
        $state->status = 'waiting';
    }

    public function handle()
    {
        PlayerUpdated::dispatch($this->player_id);
    }
}
