<?php

namespace App\Bureaucrats;

use App\DTOs\MoneyLogEntry;
use App\Events\PlayerReceivedMoney;
use App\RoundConstructor\RoundConstructor;
use App\States\OfferState;
use App\States\PlayerState;
use App\States\RoundState;

class IndexIbex extends Bureaucrat
{
    const NAME = 'Index Ibex';

    const SLUG = 'index-ibex';

    const SHORT_DESCRIPTION = 'Receive the average earnings of all Industries.';

    const DIALOG = 'The market is a fickle beast. I will help you tame it.';

    const EFFECT = 'At the end of this round, you will receive the average net earnings of all Industries from this round (including awards, expenses, and income).';

    public static function suitability(RoundConstructor $constructor): int
    {
        return $constructor->stageOfGame() === 'final-round'
            ? 0
            : 1;
    }

    public static function handleEffectAfterEndOfRound(PlayerState $player, RoundState $round, OfferState $offer)
    {
        $number_of_players = $round->game()->players->count();

        $average_earnings = $round->game()->playerStates()
            ->reduce(function ($total, $player_state) use ($round) {
                return $total + $player_state->money_history
                    ->filter(fn ($entry) => $entry->round_number === $round->round_number)
                    ->sum(fn ($entry) => $entry->amount);
            }) / $number_of_players;

        PlayerReceivedMoney::fire(
            player_id: $player->id,
            round_id: $round->id,
            amount: (int) $average_earnings,
            activity_feed_description: 'You collected the average earnings from all industries.',
            type: MoneyLogEntry::TYPE_AWARD,
        );
    }
}
