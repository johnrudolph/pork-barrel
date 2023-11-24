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

    const EFFECT_REQUIRES_DECISION = false;

    const SELECT_PROMPT = 'Select Prompt (replace me)';

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

    public static function resolveRoundStateAtEndOfRound(RoundState $round_state, PlayerState $player_state)
    {
        //
    }

    public static function resolvePlayerStateAtEndOfRound(PlayerState $player_state, RoundState $round_state)
    {
        //
    }

    public static function options(Round $round, Player $player)
    {
        return collect(range(1, 10))->mapWithKeys(function ($i) {
            return [$i => 'Placeholder option '.$i];
        });
    }
}
