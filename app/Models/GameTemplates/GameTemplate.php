<?php

namespace App\Models\GameTemplates;

class GameTemplate
{
    public function all()
    {
        return collect([
            InterestRateMadness::class,
        ]);
    }
}