<?php

namespace App\Events;

use Thunk\Verbs\Event;
use App\States\RoundState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;

class CalculatedAuctionWinners extends Event
{
    #[StateId(RoundState::class)] 
    public int $round_id;

    public function applyToRoundState(RoundState $state)
    {
        $auction_winners = collect($state->bureaucrats)
            ->mapWithKeys(function ($b) use ($state) {
                $top_offer = collect($state->offers)
                    ->map(fn ($offer) => $offer[$b])
                    ->max();

                $winning_player_ids = $top_offer > 0
                    ? collect($state->offers)
                        ->filter(fn ($offer) => $offer[$b] === $top_offer)
                        ->keys()
                        ->toArray()
                    : [];

                return [$b => [
                    'winning_player_ids' => $winning_player_ids,
                    'offer' => $top_offer,
                ]];
            });

        $state->auction_winners = $auction_winners;
    }
}
