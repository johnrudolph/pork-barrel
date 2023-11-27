<?php

namespace App\Bureaucrats;

use App\Models\Player;
use App\Models\Round;

class Watchdog extends Bureaucrat
{
    const NAME = 'Watchdog';

    const SLUG = 'watchdog';

    const SHORT_DESCRIPTION = 'See who won a bid.';

    const DIALOG = "Corruption is rampant around here. I'll sniff it out if it's the last thing I do.";

    const EFFECT = 'Select another action this round, and learn who won the bid for it.';

    public static function options(Round $round, Player $player)
    {
        return [
            'bureaucrat' => collect($round->state()->bureaucrats)
                ->reject(fn ($b) => $b === static::class)
                ->mapWithKeys(fn ($b) => [$b => $b::NAME]),
        ];
    }
}
