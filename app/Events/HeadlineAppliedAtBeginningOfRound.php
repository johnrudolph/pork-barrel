<?php

namespace App\Events;

use Thunk\Verbs\Event;
use App\States\GameState;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;

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
