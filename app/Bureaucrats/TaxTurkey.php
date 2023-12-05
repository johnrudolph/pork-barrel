<?php

namespace App\Bureaucrats;

use App\Models\Round;
use App\Models\Player;
use App\States\RoundState;
use App\States\PlayerState;
use App\Events\PlayerIncomeChanged;

class TaxTurkey extends Bureaucrat
{
    const NAME = 'Tax Turkey';

    const SLUG = 'tax-turkey';

    const SHORT_DESCRIPTION = 'Increase taxes on another industry.';

    const DIALOG = 'There are only two things certain in life: death and taxes.';

    const EFFECT = 'Choose another industry to increase their taxes. Their income will decrease by 1.';

    public static function options(Round $round, Player $player)
    {
        return [
            'player' => collect($round->game->state()->players)
                ->mapWithKeys(fn ($player_id) => [$player_id => PlayerState::load($player_id)->industry]),
        ];
    }

    public static function applyToRoundStateAtEndOfRound(RoundState $round_state, PlayerState $player_state, $amount, ?array $data = null)
    {
        PlayerIncomeChanged::fire(
            player_id: $data['player_id'],
            round_id: $round_state->id,
            amount: -1,
            activity_feed_description: 'Your taxes were increased by the Tax Turkey.',
        );
    }
}
