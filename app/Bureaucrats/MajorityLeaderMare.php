<?php

namespace App\Bureaucrats;

class MajorityLeaderMare extends Bureaucrat
{
    const NAME = 'Majority Leader Mare';

    const SLUG = 'majority-leader-mare';

    const SHORT_DESCRIPTION = 'Discount your offers next round by 1.';

    const DIALOG = "You can't get anything done around here without a majority. Scratch my back today, and I'll give you an in with the rest of the council tomorrow.";

    const EFFECT = 'Next round, each of your offers will count for 1 more money than you actually offerred.';
}
