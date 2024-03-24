<?php

namespace App\Bureaucrats;

use App\DTOs\MoneyLogEntry;
use App\Events\PlayerReceivedMoney;
use App\RoundConstructor\RoundConstructor;
use App\States\OfferState;
use App\States\PlayerState;
use App\States\RoundState;

class DoubleDonkey extends Bureaucrat
{
    const NAME = 'Double Donkey';

    const SLUG = 'double-donkey';

    const SHORT_DESCRIPTION = 'Double your earnings this round.';

    const DIALOG = 'We like to celebrate the winners in this town.';

    const EFFECT = 'After this round, receive all of your bureaucrat awards from this round again.';

    public static function suitability(RoundConstructor $constructor): int
    {
        return $constructor->stageOfGame() === 'final-round'
            ? 0
            : 1;
    }

    public static function handleEffectAfterEndOfRound(PlayerState $player, RoundState $round, OfferState $offer)
    {
        $money_earned = $player->money_history
            ->filter(fn ($entry) => $entry->type === MoneyLogEntry::TYPE_AWARD
                && $entry->round_number === $round->round_number
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
