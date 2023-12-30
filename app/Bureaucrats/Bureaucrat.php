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
            ObstructionOx::class,
            GamblinGoat::class,
            MajorityLeaderMare::class,
            MinorityLeaderMink::class,
            // OffshoreOtter::class,
            // PolicePiggy::class,
            // TaxTurkey::class,
            TreasuryChicken::class,
            Watchdog::class,
        ]);
    }

    public static function handleOnAwarded(PlayerState $player, RoundState $round, $amount, ?array $data = null)
    {
        //
    }

    public static function handleOnRoundEnd(PlayerState $player, RoundState $round, $amount, ?array $data = null)
    {
        //
    }

    public static function handleInFutureRound(PlayerState $player, RoundState $round, $amount, ?array $data = null)
    {
        //
    }

    public static function handleOnGameEnd(PlayerState $player, RoundState $round, $amount, ?array $data = null)
    {
        //
    }

    public static function options(Round $round, Player $player)
    {
        //
    }

    public static function activityFeedDescription(RoundState $state, ?array $data = null)
    {
        return 'You had the highest bid for '.static::NAME;
    }
}
