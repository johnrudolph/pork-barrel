<?php

namespace App\RoundTemplates;

use App\Bureaucrats\Bureaucrat;
use App\Bureaucrats\ObstructionOx;
use App\RoundConstructor\RoundConstructor;

class LameDuckSession extends RoundTemplate
{
    const HEADLINE = 'Lame Duck Session';

    const EFFECT = 'There are only 2 Bureaucrats this round.';

    const FLAVOR_TEXT = 'Most lawmakers are at their vacation homes upstate, but a few are still in town.';

    public static function randomlySelectedOtherBureaucrats(RoundConstructor $constructor)
    {
        $pool_of_random_bureaucrats = Bureaucrat::all()
            ->reject(fn ($b) => $b === ObstructionOx::class);

        return $constructor->selectBureaucratsFromSubset($pool_of_random_bureaucrats, 2);
    }
}
