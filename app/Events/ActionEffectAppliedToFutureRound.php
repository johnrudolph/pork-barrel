<?php

namespace App\Events;

use App\DTOs\OfferDTO;
use App\States\PlayerState;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class ActionEffectAppliedToFutureRound extends Event
{
    #[StateId(PlayerState::class)]
    public int $player_id;

    #[StateId(RoundState::class)]
    public int $round_id;

    public OfferDTO $offer;

    public function applyToPlayer(PlayerState $state)
    {
        //
    }

    public function applyToRound(RoundState $state)
    {
        $state->offers_from_previous_rounds_that_resolve_this_round
            ->push($this->offer);
    }
}
