<?php

namespace App\Industries;

use App\States\GameState;

class Industry
{
    public static function all()
    {
        return collect([
            'Barley',
            'Corn',
            'Soy Beans',
            'Sugar',
            'Tobacco',
            'Wheat',
        ]);
    }

    public static function unusedRandomIndustry(GameState $game)
    {
        return static::all()
            ->reject(fn ($i) => $game->playerStates()
                ->map(fn ($p) => $p->industry)
                ->contains($i)
            )
            ->random();
    }
}
