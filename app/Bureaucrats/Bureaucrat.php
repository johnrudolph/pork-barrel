<?php

namespace App\Bureaucrats;

class Bureaucrat
{
    const NAME = '';

    const SLUG = '';

    const SHORT_DESCRIPTION = '';

    const DIALOG = '';

    const EFFECT = '';

    const EFFECT_REQUIRES_DECISION = false;

    public static function all()
    {
        return collect([
            BailoutBunny::class,
            DisruptiveDonkey::class,
            GamblinGoat::class,
            Hawk::class,
            OffshoreOx::class,
            TaxTurkey::class,
            Watchdog::class,
        ]);
    }
}