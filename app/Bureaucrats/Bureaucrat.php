<?php

namespace App\Bureaucrats;

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

    public static function all()
    {
        return collect([
            BailoutBunny::class,
            DisruptiveDonkey::class,
            GamblinGoat::class,
            MajorityLeaderMare::class,
            MinorityLeaderMink::class,
            OffshoreOx::class,
            PolicePiggy::class,
            TaxTurkey::class,
            TreasuryChicken::class,
            Watchdog::class,
        ]);
    }

    public static function applyToPlayerStateOnPurchase(PlayerState $player_state, RoundState $round_state, array $data = null)
    {
        //
    }

    public static function applyToRoundStateOnPurchase(RoundState $round_state, PlayerState $player_state, array $data = null)
    {
        //
    }

    public static function applyToRoundStateAtEndOfRound(RoundState $round_state, PlayerState $player_state, array $data = null)
    {
        //
    }

    public static function applyToPlayerStateAtEndOfRound(PlayerState $player_state, RoundState $round_state, array $data = null)
    {
        //
    }

    public static function options(Round $round, Player $player)
    {
        //
    }

    public static function expectedData(Round $round, Player $player)
    {
        return collect(static::options($round, $player))->mapWithKeys(function ($v, $k) {
            return [$k => null];
        })->toArray();
    }
}
