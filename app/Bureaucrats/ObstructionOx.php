<?php

namespace App\Bureaucrats;

use App\Events\ActionWasBlocked;
use App\Models\Player;
use App\Models\Round;
use App\States\PlayerState;
use App\States\RoundState;

class ObstructionOx extends Bureaucrat
{
    const NAME = 'Obstruction Ox';

    const SLUG = 'obstruction-ox';

    const SHORT_DESCRIPTION = "Cancel a bureaucrat's action.";

    const DIALOG = 'Obstructionism is the only way to not get things done in this town.';

    const EFFECT = 'Select another bureaucrat this round, and cancel its action.';

    const EFFECT_REQUIRES_DECISION = true;

    const SELECT_PROMPT = 'Select a bureaucrat';

    public static function options(Round $round, Player $player)
    {
        return [
            'bureaucrat' => collect($round->state()->bureaucrats)
                ->reject(fn ($b) => $b === static::class)
                ->mapWithKeys(fn ($b) => [$b => $b::NAME]),
        ];
    }

    public static function handleOnAwarded(PlayerState $player, RoundState $round, $amount, ?array $data = null)
    {
        ActionWasBlocked::fire(
            round_id: $round->id,
            bureaucrat: $data['bureaucrat'],
            headline: 'The Obstruction Ox blocked '.$data['bureaucrat']::NAME.' from taking an action.'
        );
    }
}
