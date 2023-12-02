<?php

namespace App\Events;

use Thunk\Verbs\Event;
use App\States\RoundState;
use App\States\PlayerState;
use App\Events\PlayerReceivedMoney;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;

class EndedAuctionPhase extends Event
{
    #[StateId(RoundState::class)]
    public int $round_id;

    public function applyToRoundState(RoundState $state)
    {

    }

    public function fired(RoundState $state)
    {
        collect($state->players_with_minority_leader_mink)
            ->reject(fn ($player_id) => collect($state->offers)->pluck('player_id')->contains($player_id))
            ->each(fn ($player_id) => PlayerReceivedMoney::fire(
                player_id: $player_id,
                round_id: $this->round_id,
                activity_feed_description: 'You received money for making no offers this round. Way to stick it to them!',
                amount: 10
            ));

        // @todo arguably this is its own event and doesn't live here
        collect($state->gameState()->players)
            ->each(function ($player_id) use ($state) {
                collect($state->actionsWonBy($player_id))
                    ->each(function ($action) use ($player_id, $state) {
                        $player_state = PlayerState::load($player_id);

                        $action['bureaucrat']::applyToRoundStateOnPurchase($state, $player_state, $action['data'] ?? null);

                        $action['bureaucrat']::applyToPlayerStateOnPurchase($player_state, $state, $action['data'] ?? null);

                        PlayerSpentMoney::fire(
                            player_id: $player_id,
                            round_id: $this->round_id,
                            activity_feed_description: $action['bureaucrat']::activityFeedDescription($action['data'] ?? null),
                            amount: collect($state->offers)
                                ->filter(fn ($o) => $o['player_id'] === $player_id && $o['bureaucrat'] === $action['bureaucrat'])
                                ->first()['amount']
                        );
                    });
            });
    }
}
