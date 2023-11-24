<?php

namespace App\Bureaucrats;

class Hawk extends Bureaucrat
{
    const NAME = 'Hawk';

    const SLUG = 'hawk';

    const SHORT_DESCRIPTION = 'Decide whether or not to start a war.';

    const DIALOG = 'What we need is a common enemy, and some good old fashioned fear mongering.';

    const EFFECT = 'You will decide in the next phase whether we go to war. Starting a war increases military spending and increases fear.';

    const EFFECT_REQUIRES_DECISION = true;
}
