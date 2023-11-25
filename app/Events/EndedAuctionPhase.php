<?php

namespace App\Events;

use App\States\PlayerState;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class EndedAuctionPhase extends Event
{
    #[StateId(RoundState::class)]
    public int $round_id;

    public function applyToRoundState(RoundState $state)
    {
        $state->phase = 'decision';
    }

    public function fired(RoundState $state)
    {
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
                            activity_feed_description: 'You had the highest bid for '.$action['bureaucrat']::NAME,
                            amount: collect($state->offers)
                                ->filter(fn ($o) => $o['player_id'] === $player_id && $o['bureaucrat'] === $action['bureaucrat'])
                                ->first()['amount']
                        );
                    });
            });
    }
}
