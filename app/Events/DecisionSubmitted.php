<?php

namespace App\Events;

use App\States\PlayerState;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class DecisionSubmitted extends Event
{
    #[StateId(PlayerState::class)]
    public int $player_id;

    #[StateId(RoundState::class)]
    public int $round_id;

    public $bureaucrat;

    public ?array $data = null;

    public function applyToRoundState(RoundState $state)
    {
        $this->bureaucrat::applyToRoundStateOnDecision($state, $this->data);
    }

    public function applyToPlayerState(PlayerState $state)
    {
        $this->bureaucrat::applyToPlayerStateOnDecision($state, $this->data);
    }
}
