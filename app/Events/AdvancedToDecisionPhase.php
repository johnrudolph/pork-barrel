<?php

namespace App\Events;

use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class AdvancedToDecisionPhase extends Event
{
    #[StateId(RoundState::class)]
    public int $round_id;

    public function applyToRoundState(RoundState $state)
    {
        $state->phase = 'decision';
    }

    public function fired(RoundState $state)
    {
        CalculatedAuctionWinners::fire(round_id: $this->round_id);

        collect($state->auction_winners)
            ->each(function ($auction) {
                collect($auction['winning_player_ids'])
                    ->each(fn ($player_id) => PlayerSpentMoney::fire(
                        player_id: $player_id,
                        amount: $auction['offer'],
                        round_id: $this->round_id,
                        // @todo: get the description from their class. But how?
                        activity_feed_description: 'You won an auction.'
                    )
                    );
            });

        collect($state->gameState()->players)
            ->each(fn ($p_id) => ActionsMadeAvailableToPlayers::fire(
                player_id: $p_id,
                round_id: $this->round_id
            ));
    }
}
