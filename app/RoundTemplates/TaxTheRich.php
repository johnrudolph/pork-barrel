<?php

namespace App\RoundTemplates;

use App\DTOs\MoneyLogEntry;
use App\Events\PlayerSpentMoney;
use App\States\PlayerState;
use App\States\RoundState;

class TaxTheRich extends RoundTemplate
{
    const HEADLINE = 'Tax the rich';

    const EFFECT = 'At the end of this round, the player with the most money loses 10 money.';

    const FLAVOR_TEXT = "It's time for the 1% of the 1% of the 1% to pay their fair share!";

    public static function handleOnRoundEnd(RoundState $round_state)
    {
        $player_states = collect($round_state->game()->players)
            ->map(fn ($player_id) => PlayerState::load($player_id));

        $most_cash_held = $player_states->max(fn ($state) => $state->availableMoney()
        );

        $richest_players = $player_states->filter(fn ($state) => $state->availableMoney() === $most_cash_held
        );

        $richest_players->each(fn ($state) => PlayerSpentMoney::fire(
            player_id: $state->id,
            round_id: $round_state->id,
            activity_feed_description: 'Taxed the rich',
            amount: 10,
            type: MoneyLogEntry::TYPE_PENALIZE,
        )
        );
    }
}
