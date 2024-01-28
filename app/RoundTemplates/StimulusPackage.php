<?php

namespace App\RoundTemplates;

use App\DTOs\MoneyLogEntry;
use App\Events\PlayerReceivedMoney;
use App\Events\PlayerSpentMoney;
use App\States\PlayerState;
use App\States\RoundState;

class StimulusPackage extends RoundTemplate
{
    const HEADLINE = 'Stimulus Package';

    const EFFECT = 'Each of you has received a Stimulus Package of 15 money. If you offer less than 15 total money this round, you will lose the remainder of your Stimulus. For example, if you only offer 11 money this round, you will lose the remaining 4 of your Stimulus.';

    const FLAVOR_TEXT = 'We need to prime the pump on this economy! Build baby build!';

    public static function handleOnRoundStart(RoundState $round_state)
    {
        $player_states = collect($round_state->game()->players)
            ->map(fn ($player_id) => PlayerState::load($player_id))
            ->each(fn ($state) => PlayerReceivedMoney::fire(
                player_id: $state->id,
                round_id: $round_state->id,
                activity_feed_description: 'Stimulus Package',
                amount: 15,
                type: MoneyLogEntry::TYPE_INCOME,
            ));
    }

    public static function handleOnRoundEnd(RoundState $round_state)
    {
        $round_state->game()->playerStates()
            ->each(function ($player) use ($round_state) {
                $money_offered = $round_state->offers
                    ->filter(fn ($o) => $o->player_id === $player->id)
                    ->sum(fn ($o) => $o->netOffer());

                $stimulus_lost = 15 - $money_offered;

                if ($stimulus_lost > 0) {
                    PlayerSpentMoney::fire(
                        player_id: $player->id,
                        round_id: $round_state->id,
                        activity_feed_description: 'Lost Stimulus Package',
                        amount: $stimulus_lost,
                        type: MoneyLogEntry::TYPE_PENALIZE,
                    );
                }
            });
    }
}
