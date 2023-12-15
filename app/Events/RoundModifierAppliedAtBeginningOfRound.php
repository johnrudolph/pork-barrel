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

    public function handle()
    {
        $this->round_modifier::handleOnRoundStart(RoundState::load($this->round_id));
    }
}
