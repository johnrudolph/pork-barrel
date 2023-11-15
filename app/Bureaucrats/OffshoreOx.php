<?php

namespace App\Bureaucrats;

class OffshoreOx extends Bureaucrat
{
    const NAME = 'Offshore Ox';

    const SLUG = 'offshore-ox';

    const SHORT_DESCRIPTION = 'Hide some of your money.';

    const DIALOG = "Your profits are none of my business. I'll help you tuck them away at a nearby silo.";

    const EFFECT = 'From this point forward, every time you receive money, 25% of it will be hidden from other players.';
}
