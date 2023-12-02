<?php

namespace App\Bureaucrats;

use App\Models\Round;
use App\Models\Player;
use App\States\RoundState;
use App\States\PlayerState;
use App\Bureaucrats\Bureaucrat;
use App\Events\PlayerSpentMoney;

class Watchdog extends Bureaucrat
{
    const NAME = 'Watchdog';

    const SLUG = 'watchdog';

    const SHORT_DESCRIPTION = 'Guess who won a bureaucrat, and fine them.';

    const DIALOG = "Corruption is rampant around here. I'll sniff it out if it's the last thing I do.";

    const EFFECT = 'Select a player and a bureaucrat. If that player had the highest offer for that bureaucrat, they will be fined 5 money.';

    public static function options(Round $round, Player $player)
    {
        return [
            'bureaucrat' => [
                'title' => 'Bureaucrat',
                'description' => 'Select a bureaucrat',
                'type' => 'dropdown',
                'rules' => ['required', 'string'],
                'values' => collect($round->state()->bureaucrats)
                    ->reject(fn ($b) => $b === static::class)
                    ->mapWithKeys(fn ($b) => [$b => $b::NAME])
            ],
            'player' => $round->game->players
                ->mapWithKeys(fn ($p) => [$p->id => $p->user->name])
                ->toArray(),
        ];
    }

    // @todo: is it ok to fire an event for another player here??
    public static function applyToRoundStateAtEndOfRound(RoundState $round_state, PlayerState $player_state, array $data = null)
    {
        if($round_state->actionsWonBy($data['player'])->contains('bureaucrat', $data['bureaucrat'])) {
            PlayerSpentMoney::fire(
                player_id: $data['player'],
                round_id: $round_state->id,
                amount: 5,
                activity_feed_description: 'Fined by the Watchdog',
            );
        }
    }
}
