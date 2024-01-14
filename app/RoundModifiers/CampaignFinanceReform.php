<?php

namespace App\RoundModifiers;

use App\Events\ActionAwardedToPlayer;
use App\States\RoundState;

class CampaignFinanceReform extends RoundModifier
{
    const HEADLINE = 'Campaign Finance Reform';

    const EFFECT = 'You will receive the benefit of any bureaucrat who you offer 4 money to, even if you do not have the highest offer for them.';

    const FLAVOR_TEXT = 'A grand new experiment in democracy levels the playing field for all.';

    public static function handleOnAuctionEnd(RoundState $round_state)
    {
        $round_state->offers
            ->filter(fn ($o) => $o->netOffer() > 3 && ! $o->awarded)
            ->each(fn ($o) => ActionAwardedToPlayer::fire(
                player_id: $o->player_id,
                round_id: $o->round_id,
                activity_feed_description: $o->bureaucrat::activityFeedDescription($round_state, $o),
                offer: $o
            ));
    }
}
