<?php

namespace App\Events;

use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class RoundTemplateAppliedAtBeginningOfRound extends Event
{
    public function __construct(
        #[StateId(RoundState::class)] public int $round_id,
        public $round_template,
    ) {
    }

    public function handle()
    {
        $this->round_template::handleOnRoundStart(RoundState::load($this->round_id));
    }
}
