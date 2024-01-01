<?php

namespace App\Events;

use App\Bureaucrats\Bureaucrat;
use App\DTOs\OfferDTO;
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

    public OfferDTO $offer;

    public int $amount_modified;

    public function applyToRoundState(RoundState $state)
    {
        $state->offers
            ->filter(fn ($o) => $o->bureaucrat === $this->offer->bureaucrat && $o->player_id === $this->player_id)
            ->transform(function ($o) {
                $o->amount_modified += $this->amount_modified;

                return $o;
            });
    }

    public function applyToPlayerState(PlayerState $state)
    {
        //
    }

    public function handle()
    {
        $round = $this->state(RoundState::class);

        $round->offers_from_previous_rounds_that_resolve_this_round
            ->filter(fn ($o) => $o->bureaucrat::HOOK_TO_APPLY_IN_FUTURE_ROUND === Bureaucrat::HOOKS['on_offer_submitted'])
            ->each(fn ($o) => $o->bureaucrat::handleInFutureRound(
                PlayerState::load($o->player_id),
                RoundState::load($this->round_id),
                $o
            ));
    }
}
