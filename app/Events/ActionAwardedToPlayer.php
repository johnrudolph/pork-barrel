<?php

namespace App\Events;

use App\States\OfferState;
use App\States\PlayerState;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class ActionAwardedToPlayer extends Event
{
    #[StateId(PlayerState::class)]
    public int $player_id;

    #[StateId(RoundState::class)]
    public int $round_id;

    #[StateId(OfferState::class)]
    public $offer_id;

    public function applyToOffer(OfferState $state)
    {
        $state->awarded = true;
    }

    public function applyToPlayer(PlayerState $state)
    {
        //
    }

    public function applyToRound(RoundState $state)
    {
        //
    }

    public function handle()
    {
        $this->state(OfferState::class)->bureaucrat::handleOnAwarded(
            $this->state(PlayerState::class),
            $this->state(RoundState::class),
            $this->state(OfferState::class),
        );
    }
}
