<?php

namespace App\RoundModifiers;

use App\DTOs\MoneyLogEntry;
use App\Events\PlayerReceivedMoney;
use App\States\RoundState;

class AlwaysABridesmaid extends RoundModifier
{
    const HEADLINE = 'Always A Bridesmaid';

    const EFFECT = 'For every offer you make that is not the highest, you receive 2 money.';

    const FLAVOR_TEXT = "Some folks just aren't cut out for the big leagues.";

    public static function handleOnRoundEnd(RoundState $round_state)
    {
        $round_state->game()->players
            ->each(function ($p) use ($round_state) {
                $failed_offers = $round_state->offers
                    ->filter(fn ($o) => $o->player_id === $p && ! $o->awarded)
                    ->count();

                if ($failed_offers > 0) {
                    PlayerReceivedMoney::fire(
                        player_id: $p,
                        round_id: $round_state->id,
                        activity_feed_description: 'Received consolation prize',
                        amount: $failed_offers * 2,
                        type: MoneyLogEntry::TYPE_AWARD,
                    );
                }
            });
    }
}
