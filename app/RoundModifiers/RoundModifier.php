<?php

namespace App\RoundModifiers;

use App\RoundConstructor\RoundConstructor;
use App\States\RoundState;

class RoundModifier
{
    const HEADLINE = '';

    const EFFECT = '';

    const FLAVOR_TEXT = '';

    const NUMBER_OF_BUREAUCRATS = 4;

    public static function all()
    {
        return collect([
            AlwaysABridesmaid::class,
            Astroturfing::class,
            CampaignFinanceReform::class,
            CampaignSeason::class,
            Hegemony::class,
            LameDuckSession::class,
            LegislativeFrenzy::class,
            TaxTheRich::class,
        ]);
    }

    public static function suitability(RoundConstructor $constructor)
    {
        return 1;
    }

    public static function handleOnRoundStart(RoundState $round_state)
    {
        //
    }

    public static function handleOnAuctionEnd(RoundState $round_state)
    {
        //
    }

    public static function handleOnRoundEnd(RoundState $round_state)
    {
        //
    }
}
