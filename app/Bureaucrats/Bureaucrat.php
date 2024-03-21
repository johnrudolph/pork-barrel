<?php

namespace App\Bureaucrats;

use App\Models\Player;
use App\Models\Round;
use App\RoundConstructor\RoundConstructor;
use App\States\OfferState;
use App\States\PlayerState;
use App\States\RoundState;

class Bureaucrat
{
    const NAME = '';

    const SLUG = '';

    const SHORT_DESCRIPTION = '';

    const DIALOG = '';

    const EFFECT = '';

    const HOOK_TO_APPLY_IN_FUTURE_ROUND = '';

    const HAS_WINNER = true;

    const HOOKS = [
        'on_round_started' => 'on_round_started',
        'on_offer_submitted' => 'on_offer_submitted',
        'on_auction_ended' => 'on_auction_ended',
        'on_round_ended' => 'on_round_ended',
        'on_spent_money' => 'on_spent_money',
        'on_awaiting_results' => 'on_awaiting_results',
    ];

    public static function all()
    {
        return collect([
            BailoutBunny::class,
            BearhugBrownBear::class,
            BrinksmanshipBronco::class,
            CopyCat::class,
            CronyCrocodile::class,
            DoubleDonkey::class,
            FeeCollectingFerret::class,
            FocusedFoal::class,
            ForecastFox::class,
            FrozenFrog::class,
            FrugalFruitFly::class,
            GamblinGoat::class,
            IndexIbex::class,
            // MajorityLeaderMare::class,
            MinorityLeaderMink::class,
            MuckrakingMule::class,
            ObstructionOx::class,
            PonziPony::class,
            RejectedReindeer::class,
            SubsidySloth::class,
            TaxTurkey::class,
            TiedHog::class,
            TreasuryChicken::class,
            Watchdog::class,
        ]);
    }

    public static function perks()
    {
        return collect([
            BailoutBunny::class,
            FeeCollectingFerret::class,
            FocusedFoal::class,
            FrugalFruitFly::class,
            RejectedReindeer::class,
            TiedHog::class,
        ]);
    }

    public static function suitability(RoundConstructor $constructor): int
    {
        return 1;
    }

    public static function handleOnAwarded(PlayerState $player, RoundState $round, OfferState $offer)
    {
        // only use this if it will modify offers before they are resolved end of round
    }

    public static function handleOnRoundEnd(PlayerState $player, RoundState $round, OfferState $offer)
    {
        // this is the standard handler for most bureaucrats, and applies to each winner
    }

    public static function handleGlobalEffectOnRoundEnd(RoundState $round)
    {
        // this can handle effects that don't just apply to the winners
    }

    public static function handleInFutureRound(PlayerState $player, RoundState $round, OfferState $original_offer)
    {
        // this gets called when the effect happens in a future round, and needs to be paired with static::HOOK_TO_APPLY_IN_FUTURE_ROUND
    }

    public static function handlePerkInFutureRound(PlayerState $player, RoundState $round)
    {
        // this gets called every round for players who have the perk
    }

    public static function handleOnGameEnd(PlayerState $player, RoundState $round, OfferState $offer)
    {
        // this only gets called when the game ends
    }

    public static function options(Round $round, Player $player)
    {
        // this is used to show options and rules for livewire components
    }

    public static function activityFeedDescription(RoundState $state, OfferState $offer)
    {
        return 'You had the highest offer for '.static::NAME;
    }
}
