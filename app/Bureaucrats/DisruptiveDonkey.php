<?php

namespace App\Bureaucrats;

use App\Models\Player;
use App\Models\Round;
use App\States\RoundState;

class DisruptiveDonkey extends Bureaucrat
{
    const NAME = 'Disruptive Donkey';

    const SLUG = 'disruptive-donkey';

    const SHORT_DESCRIPTION = "Cancel a bureaucrat's action.";

    const DIALOG = 'Obstructionism is the only way to not get things done in this town.';

    const EFFECT = 'Select another bureaucrat this round, and cancel its action.';

    const EFFECT_REQUIRES_DECISION = true;

    const SELECT_PROMPT = 'Select a bureaucrat';

    public static function options(Round $round, Player $player)
    {
        return collect($round->state()->bureaucrats)
            ->reject(fn ($b) => $b === static::class)
            ->mapWithKeys(fn ($b) => [$b => $b::NAME]
            );
    }

    public static function applyToRoundStateOnDecision(RoundState $state, array $data = null)
    {
        $state->blocked_actions[] = $data['bureaucrat'];
    }
}
