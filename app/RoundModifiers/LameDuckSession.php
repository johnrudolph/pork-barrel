<?php

namespace App\RoundModifiers;

class LameDuckSession extends RoundModifier
{
    const HEADLINE = 'Lame Duck Session';

    const EFFECT = 'There are only 2 Bureaucrats this round.';

    const FLAVOR_TEXT = "Most lawmakers are at their vacation homes upstate, but a few are still in town.";

    const NUMBER_OF_BUREAUCRATS = 2;
}
