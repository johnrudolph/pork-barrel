<?php

namespace App\Events;

use App\Bureaucrats\Bureaucrat;
use App\DTOs\OfferDTO;
use App\States\OfferState;
use App\States\PlayerState;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class OfferSubmitted extends Event
{
    #[StateId(OfferState::class)]
    public ?int $offer_id = null;

    #[StateId(PlayerState::class)]
    public int $player_id;

    #[StateId(RoundState::class)]
    public int $round_id;

    public OfferDTO $offer;

    public function validate(RoundState $state)
    {
        $this->assert(
            assertion: $this->state(PlayerState::class)->availableMoney() >= $this->offer->amount_offered,
            message: 'The player does not have enough money to make this offer.'
        );

        $this->assert(
            assertion: ! $this->offer->validate()->errors()->all(),
            message: 'Offer for '.$this->offer->bureaucrat::NAME.' did not include all required fields.'
        );

        $this->assert(
            assertion: $this->offer->amount_offered > 0,
            message: 'Offer for '.$this->offer->bureaucrat::NAME.' must be greater than 0'
        );

        $this->assert(
            assertion: $state->offers()->filter(fn ($o) => $o->bureaucrat === $this->offer->bureaucrat
                    && $o->player_id === $this->player_id
            )->count() === 0,
            message: 'Player already submitted offer for '.$this->offer->bureaucrat::NAME.'.'
        );
    }

    public function applyToRoundState(RoundState $state)
    {
        $state->offer_ids->push($this->offer_id);
    }

    public function applyToOfferState(OfferState $state)
    {
        $state->player_id = $this->player_id;
        $state->round_id = $this->round_id;
        $state->bureaucrat = $this->offer->bureaucrat;
        $state->amount_offered = $this->offer->amount_offered;
        $state->data = $this->offer->data;
    }

    public function applyToPlayerState(PlayerState $state)
    {
        //
    }

    public function handle()
    {
        $round = $this->state(RoundState::class);

        $round->offers_from_previous_rounds_that_resolve_this_round
            ->filter(fn ($o) => OfferState::load($o)->bureaucrat::HOOK_TO_APPLY_IN_FUTURE_ROUND === Bureaucrat::HOOKS['on_offer_submitted'])
            ->each(fn ($o) => OfferState::load($o)->bureaucrat::handleInFutureRound(
                PlayerState::load($o->player_id),
                RoundState::load($this->round_id),
                OfferState::load($o)
            ));
    }
}
