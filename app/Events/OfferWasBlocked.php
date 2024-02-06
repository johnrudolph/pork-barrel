<?php

namespace App\Events;

use App\States\OfferState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class OfferWasBlocked extends Event
{
    #[StateId(OfferState::class)]
    public int $offer_id;

    public int $round_id;

    public function applyToRoundState(OfferState $state)
    {
        $state->is_blocked = true;
    }
}
