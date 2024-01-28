<?php

namespace App\RoundTemplates;

use App\DTOs\MoneyLogEntry;
use App\Events\PlayerReceivedMoney;
use App\States\RoundState;

class CampaignSeason extends RoundTemplate
{
    const HEADLINE = 'Campaign Season';

    const EFFECT = 'If you only make an offer to just 1 Bureaucrat this round, you receive 5 money.';

    const FLAVOR_TEXT = 'Corporations are lining up to support their favorite candidates.';

    public static function handleOnRoundEnd(RoundState $round_state)
    {
        $round_state->game()->players
            ->filter(fn ($p) => $round_state->offers->where('player_id', $p)->count() < 2)
            ->each(fn ($p) => PlayerReceivedMoney::fire(
                player_id: $p,
                round_id: $round_state->id,
                activity_feed_description: 'Received campaign kickbacks',
                amount: 5,
                type: MoneyLogEntry::TYPE_AWARD,
            )
            );
    }
}
