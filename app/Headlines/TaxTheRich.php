<?php

namespace App\Headlines;

use App\Events\PlayerSpentMoney;
use App\States\PlayerState;
use App\States\RoundState;

class TaxTheRich extends Headline
{
    const HEADLINE = 'Crackdown on the rich';

    const EFFECT = 'At the end of this round, the player with the most money loses 5 money.';

    const FLAVOR_TEXT = "It's time for the 1% of the 1% of the 1% to pay their fair share!";

    public static function applyToRoundStateAtEndOfRound(RoundState $round_state)
    {
        $player_ids = $round_state->gameState()->players;

        $most_cash_held = $player_ids->max(fn ($player_id) => 
            PlayerState::load($player_id)->money
        );

        $richest_players = $player_ids->filter(fn ($player_id) => 
            PlayerState::load($player_id)->money === $most_cash_held
        );

        $richest_players->each(fn ($player_id) => 
            PlayerSpentMoney::fire(
                player_id: $player_id,
                round_id: $round_state->id,
                activity_feed_description: 'Taxed the rich',
                amount: 5,
            )
        );
    }
}
