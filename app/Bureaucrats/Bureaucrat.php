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
            Watchdog::class,
        ]);
    }

    public static function resolveAtEndOfAuctionFor(int $player_id, int $round_id, array $data = null)
    {
        //
    }

    public static function resolveAtEndOfRoundFor(int $player_id, int $round_id, array $data = null)
    {
        //
    }

    public static function options(Round $round, Player $player)
    {
        return collect(range(1, 10))->mapWithKeys(function ($i) {
            return [$i => 'Placeholder option '.$i];
        });
    }

    public static function applyToRoundStateWhenPlayed(RoundState $state, array $data = null)
    {
        //
    }

    public static function applyToPlayerStateWhenPlayed(PlayerState $state, array $data = null)
    {
        //
    }
}
