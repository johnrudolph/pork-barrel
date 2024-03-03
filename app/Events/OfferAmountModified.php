<?php

namespace App\Events;

use App\Bureaucrats\Bureaucrat;
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

    public function fired()
    {
        $round = $this->state(RoundState::class);

        $round->offers_from_previous_rounds_that_resolve_this_round
            ->filter(fn ($o) => OfferState::load($o)->bureaucrat::HOOK_TO_APPLY_IN_FUTURE_ROUND === Bureaucrat::HOOKS['on_offer_submitted'])
            ->each(fn ($o) => OfferState::load($o)->bureaucrat::handleInFutureRound(
                PlayerState::load($o->player_id),
                RoundState::load($this->round_id),
                OfferState::load($o),
            ));
    }
}
