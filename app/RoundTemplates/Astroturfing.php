<?php

namespace App\RoundTemplates;

use App\DTOs\MoneyLogEntry;
use App\Events\PlayerReceivedMoney;
use App\States\RoundState;

class Astroturfing extends RoundTemplate
{
    const HEADLINE = 'Astroturfing';

    const EFFECT = 'For each offer you make of 3 or less, you will be paid back the amount you offered, regardless of whether you had the highest offer.';

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
                type: MoneyLogEntry::TYPE_AWARD,
            ));
    }
}
