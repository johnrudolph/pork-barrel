<?php

namespace App\Bureaucrats;

class BailoutBunny extends Bureaucrat
{
    const NAME = 'Bailout Bunny';

    const SLUG = 'bailout-bunny';

    const SHORT_DESCRIPTION = 'Get a bailout if you ever go broke.';

    const DIALOG = 'Listen, the Pork Barrel dream requires some risk, and the rich need a safety net.';

    const EFFECT = 'If you ever have less than $5, you will receive $15.';
}
