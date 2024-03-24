<?php

namespace App\Bureaucrats;

use App\DTOs\MoneyLogEntry;
use App\Events\PlayerReceivedMoney;
use App\RoundConstructor\RoundConstructor;
use App\States\OfferState;
use App\States\PlayerState;
use App\States\RoundState;

class KickbackKingfisher extends Bureaucrat
{
    const NAME = 'Kickback Kingfisher';

    const SLUG = 'kickback-kingfisher';

    const SHORT_DESCRIPTION = 'Receive 20% of earnings again.';

    const DIALOG = "The rich get richer. I don't make the rules, don't blame me.";

    const EFFECT = 'Receive 20% (rounded down) of the money you have earned from Bureaucrats so far in this game.';

    public static function suitability(RoundConstructor $constructor): int
    {
        if ($constructor->stageOfGame() === 'final-round') {
            return 2;
        }

        return 0;
    }

    public static function handleOnAwarded(PlayerState $player, RoundState $round, OfferState $offer)
    {
        $reward = $player->money_history
            ->filter(fn ($entry) => $entry->type === MoneyLogEntry::TYPE_AWARD)
            ->sum(fn ($entry) => $entry->amount) * 0.2;

        PlayerReceivedMoney::fire(
            player_id: $player->id,
            round_id: $round->id,
            amount: intval($reward),
            activity_feed_description: 'Kickbacks from the Kingfisher',
            type: MoneyLogEntry::TYPE_AWARD,
        );
    }
}
