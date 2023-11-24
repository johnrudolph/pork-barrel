<?php

namespace App\Bureaucrats;

class TreasuryChicken
 extends Bureaucrat
{
    const NAME = 'Tax Turkey';

    const SLUG = 'tax-turkey';

    const SHORT_DESCRIPTION = 'Increase taxes on another industry.';

    const DIALOG = 'There are only two things certain in life: death and taxes.';

    const EFFECT = 'Choose another industry to increase their taxes. Their revenue will decrease by 1.';
}
