<?php

namespace App\Bureaucrats;

use App\States\PlayerState;
use App\States\RoundState;

class BailoutBunny extends Bureaucrat
{
    const NAME = 'Bailout Bunny';

    const SLUG = 'bailout-bunny';

    const SHORT_DESCRIPTION = 'Get a bailout if you ever go broke.';

    const DIALOG = 'Listen, no one needs a stronger safety net than the rich.';

    const EFFECT = 'If you ever have 0 money after an auction, you will receive $10.';

    public static function applyToPlayerStateOnPurchase(PlayerState $state, RoundState $round_state, array $data = null)
    {
        $state->has_bailout = true;
    }

    public static function activityFeedDescription(array $data = null)
    {
        return "You had the highest bid for the Bailout Bunny. The next time you reach 0 money, you will receive 10 money.";
    }
}
