<?php

namespace App\Events;

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
                collect($state->actionsAvailableTo($player_id))
                    ->each(fn ($action) =>
                        PlayerSpentMoney::fire(
                            player_id: $player_id,
                            round_id: $this->round_id,
                            activity_feed_description: "You had the highest bid for ".$action::NAME,
                            amount: collect($state->offers)
                                ->filter(fn ($o) => $o['player_id'] === $player_id && $o['bureaucrat'] === $action)
                                ->first()['amount']
                        )
                    );
            });
    }
}
