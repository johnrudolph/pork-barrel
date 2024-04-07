<?php

namespace App\Bureaucrats;

use App\DTOs\MoneyLogEntry;
use App\Events\ActionEffectAppliedToFutureRound;
use App\Events\PlayerPutMoneyInTreasury;
use App\Events\PlayerReceivedMoney;
use App\States\OfferState;
use App\States\PlayerState;
use App\States\RoundState;

class TreasuryChicken extends Bureaucrat
{
    const NAME = 'Treasury Chicken';

    const SLUG = 'treasury-chicken';

    const SHORT_DESCRIPTION = 'Buy a treasury bond, and earn interest later.';

    const DIALOG = 'A penny saved is a penny earned.';

    const EFFECT = 'Invest your money in the treasury, then receive your money back with interest (rounded down) at the end of the game. The interest rate starts at 25%, but can change. This works, even if you do not have the top offer.';

    const HOOK_TO_APPLY_IN_FUTURE_ROUND = Bureaucrat::HOOKS['on_round_ended'];

    const HAS_WINNER = false;

    // @todo: implement this for all bureaucrats
    public static function effectDescription(RoundState $round, PlayerState $player)
    {
        return 'Invest your money in the treasury, then receive your money back with interest at the end of the game. This works, even if you do not have the top offer. The interest rate starts at 25%, but can change throughout the game. The current interest rate is '.$round->game()->interest_rate.'%.';
    }

    public static function handleGlobalEffectOnRoundEnd(RoundState $round)
    {
        $round->offers()->filter(fn ($o) => $o->bureaucrat === static::class)
            ->each(function ($o) use ($round) {
                $player_already_has_money_in_treasury = PlayerState::load($o->player_id)
                    ->money_in_treasury > 0;

                if (! $player_already_has_money_in_treasury) {
                    ActionEffectAppliedToFutureRound::fire(
                        player_id: $o->player_id,
                        round_id: $round->game()->round_ids->last(),
                        offer_id: $o->id,
                    );
                }

                PlayerPutMoneyInTreasury::fire(
                    player_id: $o->player_id,
                    round_id: $round->id,
                    amount: $o->netOffer(),
                );
            });
    }

    public static function handleInFutureRound(PlayerState $player, RoundState $round, OfferState $original_offer)
    {
        $interest_rate = $round->game()->interest_rate;

        $multiplier = 1 + $interest_rate;

        $text = $interest_rate * 100;

        PlayerReceivedMoney::fire(
            player_id: $player->id,
            round_id: $round->id,
            amount: intval($player->money_in_treasury * $multiplier),
            activity_feed_description: 'Received '.$text.'% return on money saved in treasury',
            type: MoneyLogEntry::TYPE_TREASURY,
        );
    }

    public static function activityFeedDescription(RoundState $state, OfferState $offer)
    {
        return 'You invested money in the Treasury. Your money is now tied up in a treasury bond, and you will get it back with interest at the end of the game.';
    }
}
