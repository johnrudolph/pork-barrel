<?php

namespace App\Bureaucrats;

use App\Models\Round;
use App\Models\Player;

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
            Hawk::class,
            MajorityLeaderMare::class,
            MinorityLeaderMink::class,
            OffshoreOx::class,
            PolicePiggy::class,
            TaxTurkey::class,
            Watchdog::class,
        ]);
    }

    public static function resolveFor(int $player_id, int $round_id, array $options = null)
    {
        //
    }

    public static function options(Round $round, Player $player)
    {
        // @todo delete this, it's bad

        return $round->game->players->mapWithKeys(function ($p) {
            return [$p->id => $p->user->name];
        });
    }
}
