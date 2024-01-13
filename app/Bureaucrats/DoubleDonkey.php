<?php

namespace App\Bureaucrats;

use App\DTOs\MoneyLogEntry;
use App\DTOs\OfferDTO;
use App\Events\ActionEffectAppliedToFutureRound;
use App\Events\PlayerReceivedMoney;
use App\States\PlayerState;
use App\States\RoundState;

class DoubleDonkey extends Bureaucrat
{
    const NAME = 'Double Donkey';

    const SLUG = 'double-donkey';

    const SHORT_DESCRIPTION = "Double your earnings this round.";

    const DIALOG = 'We like to celebrate the winners in this town.';

    const EFFECT = 'At the beginning of the next round, you will receive all of your earnings (not including income) from this round again.';

    const HOOK_TO_APPLY_IN_FUTURE_ROUND = 'on_round_started';

    public static function handleOnRoundEnd(PlayerState $player, RoundState $round, OfferDTO $offer)
    {
        ActionEffectAppliedToFutureRound::fire(
            player_id: $player->id,
            round_id: $round->game()->nextRound()->id,
            offer: $offer,
        );
    }

    public static function handleInFutureRound(PlayerState $player, RoundState $round, OfferDTO $original_offer)
    {
        $money_earned = $player->money_history
            ->filter(fn ($entry) => $entry->type !== MoneyLogEntry::TYPE_INCOME
                && $entry->round_number === $round->game()->current_round_number - 1
                && $entry->amount > 0
            )
            ->sum(fn ($entry) => $entry->amount);

        PlayerReceivedMoney::fire(
            player_id: $player->id,
            round_id: $round->id,
            amount: $money_earned,
            activity_feed_description: 'You doubled your earnings from the previous round.',
            type: MoneyLogEntry::TYPE_AWARD,
        );
    }
}
