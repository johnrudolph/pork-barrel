<?php

namespace App\Bureaucrats;

class DisruptiveDonkey extends Bureaucrat
{
    const TITLE = "Disruptive Donkey";

    const SHORT_DESCRIPTION = "Cancel another player's action.";

    const DIALOG = "Obstructionism is the only way to not get things done in this town.";

    const EFFECT = "Select another action this round, and cancel it.";
}

