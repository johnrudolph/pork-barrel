<?php

namespace App\RoundTemplates;

use App\Bureaucrats\BailoutBunny;
use App\Bureaucrats\ForecastFox;
use App\Bureaucrats\FrozenFrog;
use App\Bureaucrats\GamblinGoat;
use App\Bureaucrats\MajorityLeaderMare;
use App\Bureaucrats\MinorityLeaderMink;
use App\Bureaucrats\MuckrakingMule;
use App\Bureaucrats\SubsidySloth;
use App\Bureaucrats\TaxTurkey;
use App\Bureaucrats\TreasuryChicken;
use App\Bureaucrats\Watchdog;
use App\Events\ActionAwardedToPlayer;
use App\RoundConstructor\RoundConstructor;
use App\States\RoundState;

class CampaignFinanceReform extends RoundTemplate
{
    const HEADLINE = 'Campaign Finance Reform';

    const EFFECT = 'You will receive the benefit of any bureaucrat who you offer 4 money to, even if you do not have the highest offer for them.';

    const FLAVOR_TEXT = 'A grand new experiment in democracy levels the playing field for all.';

    public static function randomlySelectedOtherBureaucrats(RoundConstructor $constructor)
    {
        $pool_of_random_bureaucrats = collect([
            BailoutBunny::class,
            ForecastFox::class,
            FrozenFrog::class,
            GamblinGoat::class,
            MajorityLeaderMare::class,
            MinorityLeaderMink::class,
            MuckrakingMule::class,
            SubsidySloth::class,
            TaxTurkey::class,
            TreasuryChicken::class,
            Watchdog::class,
        ]);

        return $constructor->selectBureaucratsFromSubset($pool_of_random_bureaucrats, 4);
    }

    public static function handleOnAuctionEnd(RoundState $round_state)
    {
        $round_state->offers
            ->filter(fn ($o) => $o->netOffer() > 3 && ! $o->awarded)
            ->each(fn ($o) => ActionAwardedToPlayer::fire(
                player_id: $o->player_id,
                round_id: $o->round_id,
                offer: $o
            ));
    }
}
