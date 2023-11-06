<?php

namespace App\Bureaucrats;

class GamblinGoat extends Bureaucrat
{
    const NAME = "Gamblin' Goat";

    const SHORT_DESCRIPTION = 'Gamble your bid, double or nothing.';

    const DIALOG = "I've got a hair-brained scheme in the works. I'll invest the money from highest bidder and see how things shake out.";

    const EFFECT = "There's a 50% chance you'll double your bid, and a 50% chance you'll lose it all.";
}
