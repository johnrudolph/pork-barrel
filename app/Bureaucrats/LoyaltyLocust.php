<?php

namespace App\Bureaucrats;

use App\DTOs\MoneyLogEntry;
use App\Events\PlayerReceivedMoney;
use App\States\OfferState;
use App\States\PlayerState;
use App\States\RoundState;

class LoyaltyLocust extends Bureaucrat
{
    const NAME = "Loyalty Locust";

    const SLUG = 'loyalty-locust';

    const SHORT_DESCRIPTION = 'Get 2 money. Double your reward for each time you have won this before.';

    const DIALOG = "I keep my friends close, and paradoxically, my friends even closer.";

    const EFFECT = 'This will appear several times in this game. The first time you win me, receive 2 tokens. Each time you win me after that, double your previous earnings from the last time.';

    public static function handleOnRoundEnd(PlayerState $player, RoundState $round, OfferState $offer)
    {
        $number_of_previous_locust_wins = $player->money_history
            ->filter(fn($entry) => strpos($entry->description, "Loyalty Locust"))
            ->count();

        $text = match($number_of_previous_locust_wins) {
            0 => 'first',
            1 => 'second',
            2 => 'third',
            3 => 'fourth',
            4 => 'fifth',
            5 => 'sixth',
            6 => 'seventh', 
            7 => 'eighth',
        };

        $exponent = $number_of_previous_locust_wins + 1;

        PlayerReceivedMoney::fire(
            player_id: $player->id,
            round_id: $round->id,
            amount: 2 ** $exponent,
            activity_feed_description: "Loyalty Locust has rewarded you for the ".$text." time.",
            type: MoneyLogEntry::TYPE_AWARD,
        );
    }
}
