<?php

namespace App\RoundTemplates;

use App\Bureaucrats\Bureaucrat;
use App\RoundConstructor\RoundConstructor;

class PickYourPerks extends RoundTemplate
{
    const HEADLINE = 'Pick your Perks';

    const EFFECT = 'Every Bureaucrat this round offers you a Perk that will last all game.';

    const FLAVOR_TEXT = 'Every corrupt industry is corrupt in its own special way. Like a snowflake made of slime.';

    public static function suitability(RoundConstructor $constructor)
    {
        return $constructor->stageOfGame() === 'first-round'
            ? 100
            : 0;
    }

    public static function randomlySelectedOtherBureaucrats(RoundConstructor $constructor)
    {
        $pool_of_random_bureaucrats = collect(Bureaucrat::perks());

        return $constructor->selectBureaucratsFromSubset($pool_of_random_bureaucrats, 4);
    }
}
