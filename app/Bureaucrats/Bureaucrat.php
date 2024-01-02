<?php

namespace App\Bureaucrats;

use App\DTOs\OfferDTO;
use App\Models\Player;
use App\Models\Round;
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

    const HOOKS = [
        'on_round_started' => 'on_round_started',
        'on_offer_submitted' => 'on_offer_submitted',
        'on_auction_ended' => 'on_auction_ended',
        'on_round_ended' => 'on_round_ended',
    ];

    public static function all()
    {
        return collect([
            BailoutBunny::class,
            BrinksmanshipBronco::class,
            ObstructionOx::class,
            GamblinGoat::class,
            MajorityLeaderMare::class,
            MinorityLeaderMink::class,
            // OffshoreOtter::class,
            // PolicePiggy::class,
            TaxTurkey::class,
            TreasuryChicken::class,
            Watchdog::class,
        ]);
    }

    public static function handleOnAwarded(PlayerState $player, RoundState $round, OfferDTO $offer)
    {
        //
    }

    public static function handleOnRoundEnd(PlayerState $player, RoundState $round, OfferDTO $offer)
    {
        //
    }

    public static function handleGlobalEffectOnRoundEnd(RoundState $round)
    {
        //
    }

    public static function handleInFutureRound(PlayerState $player, RoundState $round, OfferDTO $original_offer)
    {
        //
    }

    public static function handleOnGameEnd(PlayerState $player, RoundState $round, OfferDTO $offer)
    {
        //
    }

    public static function options(Round $round, Player $player)
    {
        //
    }

    public static function activityFeedDescription(RoundState $state, OfferDTO $offer)
    {
        return 'You had the highest offer for '.static::NAME;
    }
}
