<?php

namespace App\Events;

use App\States\PlayerState;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class OfferSubmitted extends Event
{
    #[StateId(PlayerState::class)]
    public int $player_id;

    #[StateId(RoundState::class)]
    public int $round_id;

    public $bureaucrat;

    public $amount;

    public function applyToRoundState(RoundState $state)
    {
        $state->offers[] = [
            'player_id' => $this->player_id,
            'bureaucrat' => $this->bureaucrat,
            'amount' => $this->amount,
        ];
    }
}
