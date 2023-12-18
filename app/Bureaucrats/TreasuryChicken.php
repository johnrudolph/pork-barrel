<?php

namespace App\Bureaucrats;

use App\States\RoundState;
use App\States\PlayerState;
use App\Events\PlayerPutMoneyInTreasury;
use App\Events\ActionEffectAppliedToFutureRound;
use App\Events\PlayerReceivedMoney;

class TreasuryChicken extends Bureaucrat
{
    const NAME = 'Treasury Chicken';

    const SLUG = 'treasury-chicken';

    const SHORT_DESCRIPTION = 'Buy a treasury bond, and earn interest later.';

    const DIALOG = 'A penny saved is a penny earned.';

    const EFFECT = 'The winning bidder will spend the money now, and at the end of the game will receive their money back with 25% interest (rounded down).';

    public static function handleOnRoundEnd(PlayerState $player, RoundState $round, $amount, ?array $data = null)
    {
        PlayerPutMoneyInTreasury::fire(
            player_id: $player->id,
            round_id: $round->id,
            amount: $amount,
        );

        ActionEffectAppliedToFutureRound::fire(
            player_id: $player->id,
            round_id: $round->game()->round_ids->last(),
            bureaucrat: static::class,
            amount: $amount,
            data: $data,
            hook: $round::HOOKS['on_round_ended']
        );
    }

    public static function handleInFutureRound(PlayerState $player, RoundState $round, $amount, ?array $data = null)
    {
        PlayerReceivedMoney::fire(
            player_id: $player->id,
            round_id: $round->id,
            amount: intval($player->money_in_treasury * 1.25),
            activity_feed_description: 'Received 25% return on money saved in treasury',
        );
    }

    public static function activityFeedDescription(array $data = null)
    {
        return 'You had the highest bid for the Treasury Chicken. Your money is now tied up in a treasury bond, and you will get it back with 25% interest at the end of the game.';
    }
}
