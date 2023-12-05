<?php

namespace App\Events;

use Thunk\Verbs\Event;
use App\States\GameState;
use App\States\PlayerState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;

class PlayerAssignedToIndustry extends Event
{
    #[StateId(GameState::class)]
    public int $game_id;

    #[StateId(PlayerState::class)]
    public int $player_id;

    public $industry;

    public function applyToPlayerState(PlayerState $state)
    {
        $state->industry = $this->industry;
    }

    public function applyToGameState(GameState $state)
    {
        $state->industries [$this->player_id] = $this->industry;
    }
}
