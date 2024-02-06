<?php

namespace App\Events;

use App\Bureaucrats\Bureaucrat;
use App\States\OfferState;
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

        $round->round_template::handleOnAuctionEnd($round);

        // apply all player perks that affect auctions
        $round->game()->playerStates()
            ->each(fn ($p) => $p->perks
                ->filter(fn ($perk) => $perk::HOOK_TO_APPLY_IN_FUTURE_ROUND === Bureaucrat::HOOKS['on_auction_ended'])
                ->each(fn ($perk) => $perk::handlePerkInFutureRound(
                    PlayerState::load($p->id),
                    RoundState::load($this->round_id)
                ))
            );

        // apply any action from the previous round that affects this auction
        $round->offers_from_previous_rounds_that_resolve_this_round
            ->filter(fn ($o) => OfferState::load($o)->bureaucrat::HOOK_TO_APPLY_IN_FUTURE_ROUND === Bureaucrat::HOOKS['on_auction_ended'])
            ->each(fn ($o) => OfferState::load($o)->bureaucrat::handleInFutureRound(
                PlayerState::load(OfferState::load($o)->player_id),
                RoundState::load($this->round_id),
                OfferState::load($o),
            ));

        // award actions to players
        collect($round->game()->players)
            ->each(fn ($player_id) => $this->offersWonBy($player_id, $round)
                ->each(function ($offer) use ($player_id) {
                    ActionAwardedToPlayer::fire(
                        player_id: $player_id,
                        round_id: $this->round_id,
                        offer_id: $offer->id
                    );
                })
            );

        RoundEnded::fire(round_id: $this->round_id);
    }

    public function offersWonBy($player_id, $round)
    {
        return collect($round->offers())
            ->filter(fn ($o) => $o->player_id === $player_id)
            ->filter(function ($offer) use ($round) {
                $top_offer = collect($round->offers())
                    ->filter(fn ($o) => $o->bureaucrat === $offer->bureaucrat)
                    ->max(fn ($o) => $o->netOffer());

                return $offer->netOffer() >= $top_offer
                    && $offer->netOffer() > 0;
            });
    }
}
