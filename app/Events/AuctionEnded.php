<?php

namespace App\Events;

use Thunk\Verbs\Event;
use App\States\RoundState;
use App\States\PlayerState;
use App\Events\ActionAwardedToPlayer;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;

class AuctionEnded extends Event
{
    #[StateId(RoundState::class)]
    public int $round_id;

    public function handle()
    {
        $round = $this->state(RoundState::class);

        $round->actions_from_previous_rounds_that_resolve_this_round
            ->filter(fn ($a) => $a['hook'] === $round::HOOKS['on_auction_ended'])
            ->each(fn ($a) => $a['bureaucrat']::handleInFutureRound(
                PlayerState::load($a['player_id']),
                RoundState::load($this->round_id),
                $a['amount'], 
                $a['data'],
            ));
        
        collect($round->game()->players)
            ->each(fn ($player_id) =>
                $this->actionsWonBy($player_id, $round)
                    ->each(fn ($action) =>
                        ActionAwardedToPlayer::fire(
                            player_id: $player_id,
                            round_id: $this->round_id,
                            activity_feed_description: $action['bureaucrat']::activityFeedDescription($action['data'] ?? null),
                            bureaucrat: $action['bureaucrat'],
                            data: $action['data'] ?? null,
                            amount: $action['modified_amount'],
                        )
                    )
            );
    }

    public function actionsWonBy($player_id, $round)
    {
        return collect($round->offers)
            ->filter(fn ($o) => $o['player_id'] === $player_id)
            ->filter(function ($offer) use ($round) {
                $top_offer = collect($round->offers)
                    ->filter(fn ($o) => $o['bureaucrat'] === $offer['bureaucrat'])
                    ->max(fn ($o) => $o['modified_amount']);

                return $offer['modified_amount'] >= $top_offer
                    && $offer['modified_amount'] > 0;
            });
    }
}
