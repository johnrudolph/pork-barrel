<?php

namespace App\Bureaucrats;

use App\States\RoundState;
use App\States\PlayerState;
use App\Events\ActionEffectAppliedToFutureRound;
use App\Events\MajorityLeaderMareAppliedToNextRound;

class MajorityLeaderMare extends Bureaucrat
{
    const NAME = 'Majority Leader Mare';

    const SLUG = 'majority-leader-mare';

    const SHORT_DESCRIPTION = 'Add 1 money to each of your offers next round.';

    const DIALOG = "You can't get anything done around here without a majority. Scratch my back today, and I'll give you an in with the rest of the council tomorrow.";

    const EFFECT = 'After you submit your offers next round, 1 money will be added to each.';

    public static function handleOnRoundEnd(PlayerState $player, RoundState $round, $amount, ?array $data = null)
    {
        ActionEffectAppliedToFutureRound::fire(
            player_id: $player->id,
            round_id: $round->game()->nextRound()->id,
            bureaucrat: static::class,
            amount: $amount,
            data: $data,
            hook: $round::HOOKS['on_auction_ended']
        );
    }

    public static function handleInFutureRound(PlayerState $player, RoundState $round, $amount, ?array $data = null)
    {
        $round->offers = $round->offers->transform(function ($o) use ($player) {
            if ($o['player_id'] === $player->id) {
                $o['modified_amount'] += 1;
            }
            
            return $o;
        });
    }

    public static function activityFeedDescription(array $data = null)
    {
        return 'You had the highest bid for the Majority Leader Mare. Next round, 1 money will be added to each of your offers.';
    }
}
