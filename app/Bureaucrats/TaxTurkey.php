<?php

namespace App\Bureaucrats;

class Watchdog extends Bureaucrat
{
    const NAME = "Treasury Chicken";

    const SHORT_DESCRIPTION = "Buy a treasury bond, and earn interest later.";

    const DIALOG = "A penny saved is a penny earned.";

    const EFFECT = "The winning bidder will spend the money now, and at the end of the game will receive their money back with 50% interest (rounded down).";
}
