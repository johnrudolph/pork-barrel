<?php

namespace App\Bureaucrats;

use App\DTOs\MoneyLogEntry;
use App\DTOs\OfferDTO;
use App\Events\ActionEffectAppliedToFutureRound;
use App\Events\PlayerPutMoneyInTreasury;
use App\Events\PlayerReceivedMoney;
use App\States\PlayerState;
use App\States\RoundState;

class TreasuryChicken extends Bureaucrat
{
    const NAME = 'Treasury Chicken';

    const SLUG = 'treasury-chicken';

    const SHORT_DESCRIPTION = 'Buy a treasury bond, and earn interest later.';

    const DIALOG = 'A penny saved is a penny earned.';

    const EFFECT = 'The winning bidder will spend the money now, and at the end of the game will receive their money back with 50% interest (rounded down).';

    const HOOK_TO_APPLY_IN_FUTURE_ROUND = 'on_round_ended';

    public static function handleOnRoundEnd(PlayerState $player, RoundState $round, OfferDTO $offer)
    {
        PlayerPutMoneyInTreasury::fire(
            player_id: $player->id,
            round_id: $round->id,
            amount: $offer->amount_offered + $offer->amount_modified,
            activity_feed_description: 'You put money in the treasury. This will be returned to you with 50% interest at the end of the game.',
        );

        ActionEffectAppliedToFutureRound::fire(
            player_id: $player->id,
            round_id: $round->game()->round_ids->last(),
            offer: $offer,
        );
    }

    public static function handleInFutureRound(PlayerState $player, RoundState $round, OfferDTO $original_offer)
    {
        PlayerReceivedMoney::fire(
            player_id: $player->id,
            round_id: $round->id,
            amount: intval($player->money_in_treasury * 1.50),
            activity_feed_description: 'Received 50% return on money saved in treasury',
            type: MoneyLogEntry::TYPE_AWARD,
        );
    }

    public static function activityFeedDescription(RoundState $state, OfferDTO $offer)
    {
        return 'You had the highest bid for the Treasury Chicken. Your money is now tied up in a treasury bond, and you will get it back with 50% interest at the end of the game.';
    }
}
