<?php

namespace App\Bureaucrats;

use App\Events\ActionEffectAppliedToFutureRound;
use App\Events\PlayerReceivedMoney;
use App\States\PlayerState;
use App\States\RoundState;

class MinorityLeaderMink extends Bureaucrat
{
    const NAME = 'Minority Leader Mink';

    const SLUG = 'minority-leader-mink';

    const SHORT_DESCRIPTION = 'Earn a bonus for not making offers next round';

    const DIALOG = "The Majority will never lose if you keep bribing them. Boycott them next round and I'll make it worth your while.";

    const EFFECT = 'If you make no offers next round, you will earn 10 money.';

    const EFFECT_REQUIRES_DECISION = true;

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
        if ($round->offers->filter(fn ($o) => $o['player_id'])->count() === 0) {
            PlayerReceivedMoney::fire(
                player_id: $player->id,
                round_id: $round->id,
                amount: 10,
                activity_feed_description: "You made no offers. That'll show 'em",
            );
        }
    }

    public static function activityFeedDescription(RoundState $state, ?array $data = null)
    {
        return 'You had the highest bid for the Minority Leader Mink. Next round, you will receive 10 money if you make no offers.';
    }
}
