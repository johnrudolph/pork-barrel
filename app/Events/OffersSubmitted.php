<?php

namespace App\Events;

use Thunk\Verbs\Event;
use App\States\RoundState;
use App\States\PlayerState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;

class OffersSubmitted extends Event
{
    #[StateId(PlayerState::class)]
    public int $player_id;

    #[StateId(RoundState::class)]
    public int $round_id;

    public array $offers;

    public function applyToRoundState(RoundState $state)
    {
        $state->offers[$this->player_id] = $this->offers;
    }
}
