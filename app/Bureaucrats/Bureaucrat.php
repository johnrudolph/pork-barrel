<?php

namespace App\Bureaucrats;

class Bureaucrat
{
    const NAME = '';

    const SHORT_DESCRIPTION = '';

    const DIALOG = '';

    const EFFECT = '';

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
