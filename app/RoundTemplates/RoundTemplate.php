<?php

namespace App\RoundTemplates;

use App\Bureaucrats\Bureaucrat;
use App\RoundConstructor\RoundConstructor;
use App\States\RoundState;

class RoundTemplate
{
    const HEADLINE = '';

    const EFFECT = '';

    const FLAVOR_TEXT = '';

    public static function all()
    {
        return collect([
            AlwaysABridesmaid::class,
            Astroturfing::class,
            CampaignFinanceReform::class,
            CampaignSeason::class,
            // Hegemony::class,
            LameDuckSession::class,
            LegislativeFrenzy::class,
            PickYourPerks::class,
            StimulusPackage::class,
            TaxTheRich::class,
        ]);
    }

    public static function bureaucratsAlwaysUsedInThisTemplate()
    {
        return collect();
    }

    public static function randomlySelectedOtherBureaucrats(RoundConstructor $constructor)
    {
        $pool_of_random_bureaucrats = Bureaucrat::all();

        return $constructor->selectBureaucratsFromSubset($pool_of_random_bureaucrats, 4);
    }

    public static function selectBureaucrats(RoundConstructor $constructor)
    {
        return static::randomlySelectedOtherBureaucrats($constructor)
            ->merge(static::bureaucratsAlwaysUsedInThisTemplate())
            ->toArray();
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
