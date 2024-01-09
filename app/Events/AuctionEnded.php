<?php

namespace App\Events;

use App\Bureaucrats\Bureaucrat;
use App\States\PlayerState;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class AuctionEnded extends Event
{
    #[StateId(RoundState::class)]
    public int $round_id;

    public function handle()
    {
        $round = $this->state(RoundState::class);

        $round->offers_from_previous_rounds_that_resolve_this_round
            ->filter(fn ($o) => $o->bureaucrat::HOOK_TO_APPLY_IN_FUTURE_ROUND === Bureaucrat::HOOKS['on_auction_ended'])
            ->each(fn ($o) => $o->bureaucrat::handleInFutureRound(
                PlayerState::load($o->player_id),
                RoundState::load($this->round_id),
                $o,
            ));

        collect($round->game()->players)
            ->each(fn ($player_id) => $this->actionsWonBy($player_id, $round)
                ->each(fn ($offer) => ActionAwardedToPlayer::fire(
                    player_id: $player_id,
                    round_id: $this->round_id,
                    activity_feed_description: $offer->bureaucrat::activityFeedDescription($this->state(RoundState::class), $offer),
                    offer: $offer
                )
                )
            );

        $round->round_modifier::handleOnAuctionEnd($round);

        RoundEnded::fire(round_id: $this->round_id);
    }

    public function actionsWonBy($player_id, $round)
    {
        return collect($round->offers)
            ->filter(fn ($o) => $o->player_id === $player_id)
            ->filter(function ($offer) use ($round) {
                $top_offer = collect($round->offers)
                    ->filter(fn ($o) => $o->bureaucrat === $offer->bureaucrat)
                    ->max(fn ($o) => $o->amount_offered + $o->amount_modified);

                return $offer->amount_offered + $offer->amount_modified >= $top_offer
                    && $offer->amount_offered + $offer->amount_modified > 0;
            });
    }
}
