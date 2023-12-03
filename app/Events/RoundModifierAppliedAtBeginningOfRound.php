<?php

namespace App\Events;

use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class RoundModifierAppliedAtBeginningOfRound extends Event
{
    public function __construct(
        #[StateId(RoundState::class)] public int $round_id,
        public $round_modifier,
    ) {
    }

    public function applyToRoundState(RoundState $state)
    {
        $this->round_modifier::applyToRoundStateAtBeginningOfRound($state);
    }
}
