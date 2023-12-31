<?php

namespace App\RoundModifiers;

use App\Events\PlayerSpentMoney;
use App\States\PlayerState;
use App\States\RoundState;

class TaxTheRich extends RoundModifier
{
    const HEADLINE = 'Crackdown on the rich';

    const EFFECT = 'At the end of this round, the player with the most money loses 5 money.';

    const FLAVOR_TEXT = "It's time for the 1% of the 1% of the 1% to pay their fair share!";

    public static function handleOnRoundEnd(RoundState $round_state)
    {
        $player_states = collect($round_state->game()->players)
            ->map(fn ($player_id) => PlayerState::load($player_id));

        $most_cash_held = $player_states->max(fn ($state) => $state->money
        );

        $richest_players = $player_states->filter(fn ($state) => $state->money === $most_cash_held
        );

        $richest_players->each(fn ($state) => PlayerSpentMoney::fire(
            player_id: $state->id,
            round_id: $round_state->id,
            activity_feed_description: 'Taxed the rich',
            amount: 5,
        )
        );
    }
}
