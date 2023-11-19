<?php

namespace App\Bureaucrats;

class Watchdog extends Bureaucrat
{
    const NAME = 'Watchdog';

    const SLUG = 'watchdog';

    const SHORT_DESCRIPTION = 'See who won a bid.';

    const DIALOG = "Corruption is rampant around here. I'll sniff it out if it's the last thing I do.";

    const EFFECT = 'Select another action this round, and learn who won the bid for it.';

    const EFFECT_REQUIRES_DECISION = true;

    const SELECT_PROMPT = 'Select a bureaucrat';
}
