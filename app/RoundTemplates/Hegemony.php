<?php

namespace App\RoundTemplates;

use App\DTOs\MoneyLogEntry;
use App\Events\PlayerReceivedMoney;
use App\States\RoundState;

class Hegemony extends RoundTemplate
{
    const HEADLINE = 'Hegemony';

    const EFFECT = 'The player who makes the largest offer this round will be refunded for 50% of that offer.';

    const FLAVOR_TEXT = 'The rich get richer.';

    public static function handleOnRoundEnd(RoundState $round_state)
    {
        $max_offer = $round_state->offers()->max(fn ($o) => $o->netOffer());

        $round_state->game()->players->each(function ($p) use ($round_state, $max_offer) {
            if (
                $round_state->offers()
                    ->filter(fn ($o) => $o->player_id === $p && $o->netOffer() === $max_offer)
                    ->count() > 0
            ) {
                PlayerReceivedMoney::fire(
                    player_id: $p,
                    round_id: $round_state->id,
                    activity_feed_description: 'Received hegemony refund',
                    amount: (int) ceil($max_offer / 2),
                    type: MoneyLogEntry::TYPE_ROUND_MODIFIER_REWARD,
                );
            }
        });
    }
}
