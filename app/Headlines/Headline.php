<?php

namespace App\Headlines;

use App\Models\Player;
use App\Models\Round;
use App\States\RoundState;

class Headline
{
    const HEADLINE = '';

    const SLUG = '';

    const EFFECT = '';

    const FLAVOR_TEXT = '';

    const EFFECT_REQUIRES_DECISION = false;

    const SELECT_PROMPT = 'Select Prompt (replace me)';

    public static function all()
    {
        return collect([
            TaxTheRich::class,
        ]);
    }

    public static function applyToRoundStateAtBeginningOfRound(RoundState $round_state)
    {
        //
    }

    public static function applyToRoundStateAtEndOfRound(RoundState $round_state)
    {
        //
    }

    public static function options(Round $round, Player $player)
    {
        return collect(range(1, 10))->mapWithKeys(function ($i) {
            return [$i => 'Placeholder option '.$i];
        });
    }
}