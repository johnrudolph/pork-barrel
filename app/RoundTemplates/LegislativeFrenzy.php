<?php

namespace App\RoundTemplates;

use App\Bureaucrats\Bureaucrat;
use App\RoundConstructor\RoundConstructor;

class LegislativeFrenzy extends RoundTemplate
{
    const HEADLINE = 'Legislative Frenzy';

    const EFFECT = 'There are 5 Bureaucrats this round.';

    const FLAVOR_TEXT = 'Congress is back in session, and everyone has an agenda.';

    public static function randomlySelectedOtherBureaucrats(RoundConstructor $constructor)
    {
        $pool_of_random_bureaucrats = collect(Bureaucrat::all());

        return $constructor->selectBureaucratsFromSubset($pool_of_random_bureaucrats, 6);
    }
}
