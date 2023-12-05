<?php

namespace App\Industries;

class Industry
{
    public static function all()
    {
        return collect([
            Agriculture::class,
            Entertainment::class,
            ThePress::class,
            Weapons::class,
        ]);
    }
}