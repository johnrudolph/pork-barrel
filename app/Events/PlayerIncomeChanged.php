<?php

namespace App\Events;

use App\States\PlayerState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class PlayerIncomeChanged extends Event
{
    #[StateId(PlayerState::class)]
    public int $player_id;

    public int $round_id;

    public int $amount;

    public function apply(PlayerState $state)
    {
        $state->income += $this->amount;
    }
}
