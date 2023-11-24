<?php

namespace App\Bureaucrats;

class MinorityLeaderMink extends Bureaucrat
{
    const NAME = 'Minority Leader Mink';

    const SLUG = 'minority-leader-mink';

    const SHORT_DESCRIPTION = 'Earn a bonus for not making offers next round';

    const DIALOG = "The Majority will never lose if you keep bribing them. Boycott them next round and I'll make it worth your while.";

    const EFFECT = 'If you make no offers next round, you will earn 10 money.';

    const EFFECT_REQUIRES_DECISION = true;
}
