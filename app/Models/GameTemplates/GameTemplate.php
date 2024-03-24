<?php

namespace App\Models\GameTemplates;

use App\Bureaucrats\Bureaucrat;
use App\RoundTemplates\RoundTemplate;

class GameTemplate
{
    public static function all()
    {
        return collect([
            InterestRateMadness::class,
        ]);
    }

    public static function rounds()
    {
        return [
            1 => [
                'round_template' => RoundTemplate::class,
                'bureaucrats' => [
                    Bureaucrat::class,
                ],
            ],
            2 => [
                'round_template' => RoundTemplate::class,
                'bureaucrats' => [
                    Bureaucrat::class,
                ],
            ],
            3 => [
                'round_template' => RoundTemplate::class,
                'bureaucrats' => [
                    Bureaucrat::class,
                ],
            ],
            4 => [
                'round_template' => RoundTemplate::class,
                'bureaucrats' => [
                    Bureaucrat::class,
                ],
            ],
            5 => [
                'round_template' => RoundTemplate::class,
                'bureaucrats' => [
                    Bureaucrat::class,
                ],
            ],
            6 => [
                'round_template' => RoundTemplate::class,
                'bureaucrats' => [
                    Bureaucrat::class,
                ],
            ],
            7 => [
                'round_template' => RoundTemplate::class,
                'bureaucrats' => [
                    Bureaucrat::class,
                ],
            ],
            8 => [
                'round_template' => RoundTemplate::class,
                'bureaucrats' => [
                    Bureaucrat::class,
                ],
            ],
        ];
    }
}
