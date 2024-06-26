<?php

namespace App\Models\GameTemplates;

use App\Bureaucrats\BailoutBunny;
use App\Bureaucrats\BearhugBrownBear;
use App\Bureaucrats\ConsolationCow;
use App\Bureaucrats\CronyCrocodile;
use App\Bureaucrats\DoubleDonkey;
use App\Bureaucrats\EqualityElk;
use App\Bureaucrats\FeeCollectingFerret;
use App\Bureaucrats\FocusedFoal;
use App\Bureaucrats\ForecastFox;
use App\Bureaucrats\FrozenFrog;
use App\Bureaucrats\FrugalFruitFly;
use App\Bureaucrats\GamblinGoat;
use App\Bureaucrats\IndexIbex;
use App\Bureaucrats\InterestInchworm;
use App\Bureaucrats\KickbackKingfisher;
use App\Bureaucrats\LoyaltyLocust;
use App\Bureaucrats\MajorityLeaderMare;
use App\Bureaucrats\MinorityLeaderMink;
use App\Bureaucrats\ObstructionOx;
use App\Bureaucrats\PonziPony;
use App\Bureaucrats\RejectedReindeer;
use App\Bureaucrats\TaxTurkey;
use App\Bureaucrats\TiedHog;
use App\Bureaucrats\TreasuryChicken;
use App\Bureaucrats\Watchdog;
use App\RoundTemplates\AlwaysABridesmaid;
use App\RoundTemplates\Astroturfing;
use App\RoundTemplates\CampaignFinanceReform;
use App\RoundTemplates\CampaignSeason;
use App\RoundTemplates\LameDuckSession;
use App\RoundTemplates\LegislativeFrenzy;
use App\RoundTemplates\StimulusPackage;
use App\RoundTemplates\TaxTheRich;

class InterestRateMadness extends GameTemplate
{
    public static function rounds()
    {
        return [
            1 => [
                'round_template' => AlwaysABridesmaid::class,
                'bureaucrats' => [
                    BailoutBunny::class,
                    RejectedReindeer::class,
                    TiedHog::class,
                    FrugalFruitFly::class,
                    CronyCrocodile::class,
                ],
            ],
            2 => [
                'round_template' => CampaignSeason::class,
                'bureaucrats' => [
                    FocusedFoal::class,
                    FeeCollectingFerret::class,
                    MinorityLeaderMink::class,
                    EqualityElk::class,
                    LoyaltyLocust::class,
                ],
            ],
            3 => [
                'round_template' => LameDuckSession::class,
                'bureaucrats' => [
                    ForecastFox::class,
                    TreasuryChicken::class,
                ],
            ],
            4 => [
                'round_template' => StimulusPackage::class,
                'bureaucrats' => [
                    IndexIbex::class,
                    PonziPony::class,
                    DoubleDonkey::class,
                    LoyaltyLocust::class,
                    TreasuryChicken::class,
                ],
            ],
            5 => [
                'round_template' => Astroturfing::class,
                'bureaucrats' => [
                    BearhugBrownBear::class,
                    Watchdog::class,
                    FrozenFrog::class,
                    GamblinGoat::class,
                ],
            ],
            6 => [
                'round_template' => TaxTheRich::class,
                'bureaucrats' => [
                    TaxTurkey::class,
                    GamblinGoat::class,
                    ObstructionOx::class,
                    LoyaltyLocust::class,
                    TreasuryChicken::class,
                ],
            ],
            7 => [
                'round_template' => CampaignFinanceReform::class,
                'bureaucrats' => [
                    MajorityLeaderMare::class,
                    InterestInchworm::class,
                    BearhugBrownBear::class,
                    ForecastFox::class,
                    LoyaltyLocust::class,
                ],
            ],
            8 => [
                'round_template' => LegislativeFrenzy::class,
                'bureaucrats' => [
                    GamblinGoat::class,
                    DoubleDonkey::class,
                    InterestInchworm::class,
                    LoyaltyLocust::class,
                    ConsolationCow::class,
                    KickbackKingfisher::class,
                ],
            ],
        ];
    }
}
