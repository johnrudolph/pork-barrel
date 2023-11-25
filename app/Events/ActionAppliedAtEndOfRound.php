<?php

namespace App\Events;

use App\States\PlayerState;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class ActionAppliedAtEndOfRound extends Event
{
    #[StateId(RoundState::class)]
    public int $round_id;

    #[StateId(PlayerState::class)]
    public int $player_id;

    public $bureaucrat;

    public $data;

    public function applyToRoundState(RoundState $state)
    {
        $this->bureaucrat::applyToRoundStateAtEndOfRound($state, PlayerState::load($this->player_id), $this->data);
    }

    public function applyToPlayerState(PlayerState $state)
    {
        $this->bureaucrat::applyToPlayerStateAtEndOfRound($state, RoundState::load($this->round_id), $this->data);
    }
}
