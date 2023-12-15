<?php

namespace App\Bureaucrats;

use App\Events\PlayerSpentMoney;
use App\Models\Player;
use App\Models\Round;
use App\States\PlayerState;
use App\States\RoundState;

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
            'bureaucrat' => collect($round->state()->bureaucrats)
                ->reject(fn ($b) => $b === static::class)
                ->mapWithKeys(fn ($b) => [$b => $b::NAME]),
            'player' => $round->game->players
                ->mapWithKeys(fn ($p) => [$p->id => $p->user->name])
                ->toArray(),
        ];
    }

    public static function handleOnRoundEnd(PlayerState $player, RoundState $round, $amount, ?array $data = null)
    {
        if (
            $round->actions_awarded->filter(fn ($a) => 
                $a['bureaucrat'] === $data['bureaucrat']
                && $a['player'] === $data['player']
            )
        ) {
            PlayerSpentMoney::fire(
                player_id: $data['player'],
                round_id: $round->id,
                amount: 5,
                activity_feed_description: 'Fined by the Watchdog. Bribery is not tolarated around these parts.',
            );
        }
    }
}
