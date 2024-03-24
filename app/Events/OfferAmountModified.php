<?php

namespace App\Events;

use App\States\OfferState;
use App\States\PlayerState;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class OfferAmountModified extends Event
{
    #[StateId(PlayerState::class)]
    public int $player_id;

    #[StateId(RoundState::class)]
    public int $round_id;

    #[StateId(OfferState::class)]
    public int $offer_id;

    public int $amount_modified;

    public string $modifier_description;

    public bool $is_charged_to_player;

    public function applyToOffer(OfferState $state)
    {
        $state->amount_modifications[] = [
            'amount' => $this->amount_modified,
            'description' => $this->modifier_description,
            'charged_to_player' => $this->is_charged_to_player,
        ];
    }

    public function applyToRoundState(RoundState $state)
    {
        //
    }

    public function applyToPlayerState(PlayerState $state)
    {
        //
    }
}
