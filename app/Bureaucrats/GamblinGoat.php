<?php

namespace App\Bureaucrats;

use App\DTOs\MoneyLogEntry;
use App\Events\PlayerReceivedMoney;
use App\States\OfferState;
use App\States\PlayerState;
use App\States\RoundState;

class GamblinGoat extends Bureaucrat
{
    const NAME = "Gamblin' Goat";

    const SLUG = 'gamblin-goat';

    const SHORT_DESCRIPTION = 'Get a random return of money.';

    const DIALOG = "I've got a hair-brained scheme in the works. No promises, but I think it'll pay off big time.";

    const EFFECT = 'I will give you a random return of 1-10 money.';

    public static function handleOnRoundEnd(PlayerState $player, RoundState $round, OfferState $offer)
    {
        PlayerReceivedMoney::fire(
            player_id: $player->id,
            round_id: $round->id,
            amount: rand(1, 10),
            activity_feed_description: "The Gamlin' Goat's scheme paid off!",
            type: MoneyLogEntry::TYPE_BUREAUCRAT_REWARD,
        );
    }

    public static function activityFeedDescription(RoundState $state, OfferState $offer)
    {
        return "You had the highest bid for the Gamblin' Goat. Let's see how it pays off...";
    }
}
