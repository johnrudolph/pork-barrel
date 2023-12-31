<?php

namespace App\Events;

use App\DTOs\OfferDTO;
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

    public OfferDTO $offer;

    public function validate(RoundState $state)
    {
        $this->assert(
            assertion: ! $this->offer->validate()->errors()->all(),
            message: 'Offer for '.$this->offer->bureaucrat::NAME.' did not include all required fields'
        );
    }

    public function applyToRoundState(RoundState $state)
    {
        $state->offers->push($this->offer);
    }

    public function applyToPlayerState(PlayerState $state)
    {
        //
    }

    public function handle()
    {
        $round = $this->state(RoundState::class);

        $round->actions_from_previous_rounds_that_resolve_this_round
            ->filter(fn ($a) => $a['hook'] === $round::HOOKS['on_offer_submitted'])
            ->each(fn ($a) => $a['bureaucrat']::handleInFutureRound(
                PlayerState::load($a['player_id']),
                RoundState::load($this->round_id),
                $a['amount'],
                $a['data'],
            ));
    }
}
