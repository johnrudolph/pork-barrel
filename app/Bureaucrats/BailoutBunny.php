<?php

namespace App\Bureaucrats;

use App\States\PlayerState;

class BailoutBunny extends Bureaucrat
{
    const NAME = 'Bailout Bunny';

    const SLUG = 'bailout-bunny';

    const SHORT_DESCRIPTION = 'Get a bailout if you ever go broke.';

    const DIALOG = 'Listen, no one needs a stronger safety net than the rich.';

    const EFFECT = 'If you ever have 0 money after an auction, you will receive $10.';

    public static function applyToPlayerStateOnPurchase(PlayerState $state)
    {
        $state->has_bailout = true;
    }
}
