<?php

namespace App\Industries;

use App\States\GameState;

class Industry
{
    public static function all()
    {
        return collect([
            'Apples',
            'Barley',
            'Beets',
            'Blueberries',
            'Corn',
            'Coffee',
            'Cotton',
            'Hay',
            'Hemp',
            'Hops',
            'Lettuce',
            'Oranges',
            'Peaches',
            'Pears',
            'Peanuts',
            'Potatoes',
            'Raspberries',
            'Soy Beans',
            'Strawberries',
            'Sugar',
            'Tea',
            'Tobacco',
            'Tomatoes',
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
