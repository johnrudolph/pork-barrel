<?php

namespace App\RoundModifiers;

use App\Events\PlayerReceivedMoney;
use App\States\RoundState;

class Astroturfing extends RoundModifier
{
    const HEADLINE = 'Astroturfing';

    const EFFECT = 'For each offer you make of 3 or less, you will receive the amount offerred in return regardless of whether you had the highest offer.';

    const FLAVOR_TEXT = 'The people are speaking, but who are they?';

    public static function handleOnAuctionEnd(RoundState $round_state)
    {
        $round_state->offers
            ->filter(fn ($o) => $o->netOffer() < 4)
            ->each(fn ($o) => PlayerReceivedMoney::fire(
                player_id: $o->player_id,
                round_id: $o->round_id,
                activity_feed_description: 'Received astroturfing refund',
                amount: $o->netOffer(),
            ));
    }
}
