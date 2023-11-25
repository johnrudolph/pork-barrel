<?php

namespace App\Events;

use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class HeadlineAppliedAtBeginningOfRound extends Event
{
    public function __construct(
        #[StateId(RoundState::class)] public int $round_id,
        public $headline,
    ) {
    }

    public function applyToRoundState(RoundState $state)
    {
        $this->headline::applyToRoundStateAtBeginningOfRound($state);
    }
}
