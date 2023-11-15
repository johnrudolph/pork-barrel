<?php

namespace App\Bureaucrats;

class GamblinGoat extends Bureaucrat
{
    const NAME = "Gamblin' Goat";

    const SLUG = 'gamblin-goat';

    const SHORT_DESCRIPTION = 'Get a random return of money.';

    const DIALOG = "I've got a hair-brained scheme in the works. No promises, but I think it'll pay off big time.";

    const EFFECT = "Get a random return of 1-10 money.";
}
