<?php

namespace App\Events;

use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class EndedDecisionPhase extends Event
{
    #[StateId(RoundState::class)]
    public int $round_id;

    public function applyToRoundState(RoundState $state)
    {
        //
    }

    public function fired(RoundState $state)
    {
        ActionsResolved::fire(round_id: $this->round_id);
    }
}
